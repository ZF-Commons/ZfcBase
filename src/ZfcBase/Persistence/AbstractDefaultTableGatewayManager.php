<?php

namespace ZfcBase\Persistence;

use ArrayObject;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Stdlib\Hydrator\HydratorInterface;
use ZfcBase\EventManager\EventProvider;
use ZfcBase\Persistence\DefaultObjectManagerOptions;
use ZfcBase\Util\String;

abstract class AbstractDefaultTableGatewayManager extends EventProvider implements
    DefaultFinderInterface,
    ObjectManagerInterface
{
    /**
     * @var DefaultObjectManagerOptions
     */
    protected $options;

    /**
     * @var AbstractTableGateway
     */
    protected $tableGateway;

    /**
     * @var HydratorInterface
     */
    protected $hydrator;


    /**
     * @param DefaultObjectManagerOptions $options
     */
    public function setOptions(DefaultObjectManagerOptions $options)
    {
        $this->options = $options;
    }

    /**
     * @return DefaultObjectManagerOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param AbstractTableGateway $tableGateway
     */
    public function setTableGateway(AbstractTableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    /**
     * @return AbstractTableGateway
     */
    public function getTableGateway()
    {
        return $this->tableGateway;
    }

    /**
     * @param HydratorInterface $hydrator
     */
    public function setHydrator(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
    }

    /**
     * @return HydratorInterface
     */
    public function getHydrator()
    {
        return $this->hydrator;
    }

    abstract protected function fromRow($row);

    /**
     * Persists a mapped object
     *
     * @param object $object
     * @return object
     * @throws Exception\RuntimeException
     * @throws Exception\InvalidArgumentException
     */
    public function persist($object)
    {
        if (!is_object($object) || get_class($object) !== $this->getOptions()->getClassName()) {
            throw new Exception\InvalidArgumentException('$object must be an instance of ' . $this->getOptions()->getClassName());
        }
        $data = $this->getHydrator()->extract($object);
        $this->events()->trigger(__FUNCTION__ . '.pre', $this, array('data' => $data, 'object' => $object));
        $idGetter = $this->fieldToGetterMethod($this->getOptions()->getPrimaryKey());
        if ($object->$idGetter() > 0) {
            $this->getTableGateway()->update((array) $data, array($this->getOptions()->getPrimaryKey() => $object->$idGetter()));
        } else {
            $this->getTableGateway()->insert((array) $data);
            if (!$this->getTableGateway() instanceof AbstractTableGateway) {
                throw new Exception\RuntimeException(
                    get_class($this->getTableGateway()) . ' is not an instance of '
                        . 'Zend\Db\TableGateway\AbstractTableGateway. This is needed, to have access to the db adapter'
                );
            }
            $id = $this->getTableGateway()->getAdapter()->getDriver()->getLastGeneratedValue();
            $idSetter = $this->fieldToSetterMethod($this->getOptions()->getPrimaryKey());
            $object->$idSetter($id);
        }
        $this->events()->trigger(__FUNCTION__ . '.post', $this, array('data' => $data, 'object' => $object));
        return $object;
    }

    /**
     * @param $id
     * @return object
     */
    public function find($id)
    {
        $result = $this->events()->trigger(__FUNCTION__ . '.pre', $this, compact('id'));
        if ($result->stopped()) {
            return $result->last();
        }
        $rowset = $this->getTableGateway()->select(array($this->getOptions()->getPrimaryKey() => $id));
        $row = $rowset->current();
        $object = $this->fromRow($row);
        $this->events()->trigger(__FUNCTION__ . '.post', $this, array('object' => $object, 'row' => $row));
        return $object;
    }

    protected function fieldToSetterMethod($name)
    {
        return 'set' . String::toCamelCase($name);
    }

    protected static function fieldToGetterMethod($name)
    {
        return 'get' . String::toCamelCase($name);
    }
}