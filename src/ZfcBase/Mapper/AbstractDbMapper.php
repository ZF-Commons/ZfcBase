<?php

namespace ZfcBase\Mapper;

use ReflectionProperty;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use ZfcBase\Mapper\MappingHydrator;
use ZfcBase\Persistence\ObjectManagerInterface;
use ZfcBase\Persistence\DefaultFinderInterface;
use ZfcBase\Persistence\DefaultObjectManagerOptions;

abstract class AbstractDbMapper implements
    DefaultFinderInterface,
    ObjectManagerInterface
{
    /**
     * @var Adapter
     */
    protected $dbAdapter;

    /**
     * @var MappingHydrator
     */
    protected $hydrator;

    /**
     * @var object
     */
    protected $modelPrototype;

    /**
     * @var DefaultObjectManagerOptions
     */
    protected $options;

    /**
     * @return Select
     */
    public function select()
    {
        $select = new Select();
        return $select;
    }

    /**
     * @param Select $select
     * @return HydratingResultSet
     */
    public function selectWith(Select $select)
    {
        $adapter = $this->getDbAdapter();
        $statement = $adapter->createStatement();
        $select->prepareStatement($adapter, $statement);
        $result = $statement->execute();

        $resultSet = new \Zend\Db\ResultSet\HydratingResultSet();
        $resultSet->setRowObjectHydrator($this->getHydrator());
        $resultSet->setRowObjectPrototype($this->getModelPrototype());
        $resultSet->setDataSource($result);

        return $resultSet;
    }

    public function persist($object)
    {
        $className = $this->getOptions()->getClassName();
        if (!$object instanceof $className) {
            throw new Exception\InvalidArgumentException(
                '$entity must be an instance of ' . $className
            );
        }
        if ($this->getIdentifier($object)) {
            $this->update($object);
        } else {
            $this->insert($object);
        }
    }

    /**
     * check if object has already an identifier
     *
     * @param object $object
     * @return mixed
     */
    protected function getIdentifier($object)
    {
        $property = new ReflectionProperty(get_class($object), $this->getPrimaryKey());
        $property->setAccessible(true);
        return $property->getValue($object);
    }

    public function find($id)
    {
        $select = $this->select();
        $select->from($this->getTableName());
        $pkField = $this->getHydrator()->getFieldForProperty($this->getPrimaryKey());
        $select->where(array($pkField => $id));

        $results = $this->selectWith($select);
        $row = null;
        if ($results) {
            $row = $results->current();
        }
        return $row;
    }

    /**
     * @param object $object
     * @return bool
     */
    public function remove($object)
    {
        $className = $this->getOptions()->getClassName();
        if (!$object instanceof $className) {
            throw new Exception\InvalidArgumentException(
                '$entity must be an instance of ' . $className
            );
        }
        if ($value = $this->getIdentifier($object)) {
            $sql = new Sql($this->getDbAdapter(), $this->getTableName());
            $delete = $sql->delete();
            $pkField = $this->getHydrator()->getFieldForProperty($this->getPrimaryKey());
            $delete->where(array($pkField => $value));
            $statement = $sql->prepareStatementForSqlObject($delete);
            $result = $statement->execute();
            return true;
        }
        return false;
    }

    protected function insert($entity)
    {
        $hydrator = $this->getHydrator();
        $set = $hydrator->extract($entity);
        $pkField = $this->getHydrator()->getFieldForProperty($this->getPrimaryKey());
        unset($set[$pkField]);

        $sql = new Sql($this->getDbAdapter(), $this->getTableName());
        $insert = $sql->insert();
        $insert->values($set);

        $statement = $sql->prepareStatementForSqlObject($insert);
        $result = $statement->execute();
        $lastInsertValue = $this->getDbAdapter()->getDriver()->getConnection()->getLastGeneratedValue();

        $property = new ReflectionProperty(get_class($entity), $this->getPrimaryKey());
        $property->setAccessible(true);
        $property->setValue($entity, $lastInsertValue);
    }

    protected function update($entity)
    {
        $pk = $this->getPrimaryKey();
        $hydrator = $this->getHydrator();
        $set = $hydrator->extract($entity);

        $pkField = $this->getHydrator()->getFieldForProperty($pk);
        $pkValue = $set[$pkField];
        unset($set[$pkField]);

        $sql = new Sql($this->getDbAdapter(), $this->getTableName());
        $update = $sql->update();
        $update->set($set);
        $update->where(array($pkField => $pkValue));

        $statement = $sql->prepareStatementForSqlObject($update);
        var_dump($statement->getSql());
        try {
            $result = $statement->execute();

        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }

        return $result->getAffectedRows();
    }

    /**
     * @return object
     */
    public function getModelPrototype()
    {
        if (!$this->modelPrototype) {
            $className = $this->getOptions()->getClassName();
            $this->modelPrototype = new $className;
        }
        return $this->modelPrototype;
    }

    /**
     * @return Adapter
     */
    public function getDbAdapter()
    {
        return $this->dbAdapter;
    }

    /**
     * @param Adapter $dbAdapter
     * @return AbstractDbMapper
     */
    public function setDbAdapter(Adapter $dbAdapter)
    {
        $this->dbAdapter = $dbAdapter;
        return $this;
    }

    /**
     * @return MappingHydrator
     */
    public function getHydrator()
    {
        if (!$this->hydrator instanceof MappingHydrator) {
            $map = $this->getOptions()->getMap();
            $hydrator = new MappingHydrator($map);
            $this->hydrator = $hydrator;
        }
        return $this->hydrator;
    }

    /**
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->getOptions()->getPrimaryKey();
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->getOptions()->getTableName();
    }

    /**
     * Flush - not support by db mapper, does nothing
     *
     * @return void
     */
    public function flush()
    {
        // not supported in Db Mapper, do nothing
    }

    /**
     * Finds all objects in the repository.
     *
     * @return mixed The objects.
     */
    public function findAll()
    {
        // TODO: Implement findAll() method.
    }

    /**
     * Finds objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed. An implementation may throw
     * an UnexpectedValueException if certain values of the sorting or limiting details are
     * not supported.
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return mixed The objects.
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        // TODO: Implement findBy() method.
    }

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array $criteria
     * @return object The object.
     */
    public function findOneBy(array $criteria)
    {
        $mappedCriteria = array();
        foreach ($criteria as $property => $value) {
            $field = $this->getHydrator()->getFieldForProperty($property);
            $mappedCriteria[$field] = $value;
        }
        $select = $this->select();
        $select->from($this->getTableName());
        $select->where($mappedCriteria);
        $results = $this->selectWith($select);
        $row = null;
        if ($results) {
            $row = $results->current();
        }
        return $row;
    }

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


}