<?php

namespace ZfcBase\Mapper;

use ArrayObject;
use Traversable;
use Zend\Db\TableGateway\TableGatewayInterface;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\TableGateway\TableGateway;
use ZfcBase\EventManager\EventProvider;
use ZfcUser\Module as ZfcUser;

abstract class AbstractDbMapper extends EventProvider
{
    /**
     * tableGateway 
     * 
     * @var TableGatewayInterface
     */
    protected $tableGateway;

    /**
     * Get table name
     *
     * @return string
     */
    abstract public function getTableName();

    /**
     * Get primary key
     *
     * @return string
     */
    abstract public function getPrimaryKey();

    /**
     * Get tableGateway.
     *
     * @return TableGatewayInterface
     */
    public function getTableGateway()
    {
        return $this->tableGateway;
    }
 
    /**
     * Set tableGateway.
     *
     * @param TableGatewayInterface $tableGateway
     */
    public function setTableGateway(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        return $this;
    }

    /**
     * toScalarValueArray 
     * 
     * @param array $values 
     * @return array
     */
    protected function toScalarValueArray($values) {
        //convert object toArray first
        if(is_object($values)) {
            if(is_callable(array($values, 'toScalarValueArray'))) {
                return $values->toScalarValueArray();
            }
            
            if(is_callable(array($values, 'toArray'))) {
                $values = $values->toArray();
            }
        }
        
        if(!is_array($values)) {
            throw new Exception\InvalidArgumentException("Parameter is not an array");
        }
        
        $ret = array();
        foreach($values as $key => $value) {
            if(is_scalar($value)) {
                $ret[$key] = $value;
                continue;
            }
            if(is_object($value)) {
                $ret[$key] = $this->convertObjectToScalar($value);
                continue;
            }
            if($value == null) {
                $ret[$key] = null;
                continue;
            }
            
            throw new Exception\InvalidArgumentException("Can not convert '$key' key value to string");
        }
        
        return $ret;
    }

    /**
     * convertObjectToScalar 
     * 
     * @param mixed $obj 
     * @access string
     * @return void
     */
    protected function convertObjectToScalar($obj) {

        if(is_callable(array($obj, '__toString'))) {
            return $obj->__toString();
        }
        if($obj instanceof \DateTime) {
            return $obj->format('Y-m-d\TH:i:s');
        }
        
        throw new Exception\InvalidArgumentException("Can not convert object '" . get_class($obj) . "' to string");
    }

    /**
     * @param $id
     * @return object
     */
    public function find($id)
    {
        $rowset = $this->getTableGateway()->select(array($this->getPrimaryKey() => $id));
        $row = $rowset->current();
        $model = $this->fromRow($row);
        $this->events()->trigger(__FUNCTION__ . '.post', $this, array('model' => $model, 'row' => $row));
        return $model;
    }

    abstract protected function fromRow($row);

    /**
     * Persists a mapped object
     *
     * @param object $model
     * @return object
     * @throws Exception\RuntimeException
     * @throws Exception\InvalidArgumentException
     */
    public function persist($model)
    {
        if (!is_object($model) || \get_class($model) !== $this->getClassName()) {
            throw new Exception\InvalidArgumentException('$model must be an instance of ' . $this->getClassName());
        }
        $data = new ArrayObject($this->toScalarValueArray($model)); // or perhaps pass it by reference?
        $this->events()->trigger(__FUNCTION__ . '.pre', $this, array('data' => $data, 'model' => $model));
        $idGetter = AbstractModel::fieldToGetterMethod($this->getPrimaryKey());
        if ($model->$idGetter() > 0) {
            $this->getTableGateway()->update((array) $data, array($this->getPrimaryKey() => $model->$idGetter()));
        } else {
            $this->getTableGateway()->insert((array) $data);
            if (!$this->getTableGateway() instanceof AbstractTableGateway) {
                throw new Exception\RuntimeException(
                    get_class($this->getTableGateway()) . ' is not an instance of '
                    . 'Zend\Db\TableGateway\AbstractTableGateway. This is needed, to have access to the db adapter'
                );
            }
            $id = $this->getTableGateway()->getAdapter()->getDriver()->getLastGeneratedValue();
            $idSetter = AbstractModel::fieldToSetterMethod($this->getPrimaryKey());
            $model->$idSetter($id);
        }
        return $model;
    }

}
