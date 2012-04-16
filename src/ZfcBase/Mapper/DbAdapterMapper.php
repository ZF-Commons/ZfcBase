<?php

namespace ZfcBase\Mapper;

use Zend\Db\Adapter\Adapter,
    Zend\Db\Adapter\AdapterAwareInterface,
    Zend\Db\TableGateway\TableGateway,
    InvalidArgumentException as CannotConvertToScalarException,
    InvalidArgumentException as NotArrayException;

abstract class DbAdapterMapper implements TransactionalInterface, AdapterAwareInterface {
    /**
     * @var array
     */
    private static $transactionCount = array();
    
    /**
     * Database adapter for read queries
     *
     * @var Zend\Db\Adapter\Adapter
     */
    protected $readAdapter;

    /**
     * Database adapter for write queries
     *
     * @var Zend\Db\Adapter\Adapter
     */
    protected $writeAdapter;
    
    private $tableGateways = array();
    
    /**
     * @param string $tableName
     * @param bool $write
     * @return Zend\Db\TableGateway\TableGateway 
     */
    protected function getTableGateway($tableName, $write = false) {
        $typeStr = $write ? 'write' : 'read';
        
        //checks for existing instance
        if(isset($this->tableGateways[$typeStr][$tableName])) {
            return $this->tableGateways[$typeStr][$tableName];
        }
        
        $adapter = $write ? $this->getWriteAdapter() : $this->getReadAdapter();
        $tableGateway = new TableGateway($tableName, $adapter);
        
        //keep the instance
        $this->tableGateways[$typeStr][$tableName] = $tableGateway;
        
        return $tableGateway;
    }
    
    protected function toScalarValueArray($values) {
        //convert object to array first
        if(is_object($values)) {
            if(is_callable(array($values, 'toScalarValueArray'))) {
                return $values->toScalarValueArray();
            }
            
            if(is_callable(array($values, 'toArray'))) {
                $values = $values->toArray();
            }
        }
        
        if(!is_array($values)) {
            throw new NotArrayException("Parameter is not an array");
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
            
            throw new CannotConvertToScalarException("Can not convert '$key' key value to string");
        }
        
        return $ret;
    }
    
    protected function convertObjectToScalar($obj) {
        if(is_callable(array($obj, '__toString'))) {
            return $obj->__toString();
        }
        if($obj instanceof \DateTime) {
            return $obj->format('Y-m-d\TH:i:sP');
        }
        
        throw new CannotConvertToScalarException("Can not convert object '" . get_class($obj) . "' to string");
    }
    
    public function beginTransaction() {
        $this->performTransactionOperation('beginTransaction', $this->getWriteAdapter());
    }
    
    public function commit() {
        $this->performTransactionOperation('commit', $this->getWriteAdapter());
    }
    
    public function rollback() {
        $this->performTransactionOperation('rollback', $this->getWriteAdapter());
    }
    
    private function performTransactionOperation($operation, Adapter $adapter) {
        $adapterHash = spl_object_hash($adapter);
        if(!isset(self::$transactionCount[$adapterHash])) {
            self::$transactionCount[$adapterHash] = 0;
        }
        
        switch($operation) {
            case 'beginTransaction':
                if(self::$transactionCount[$adapterHash] == 0) {
                    $adapter->getDriver()->getConnection()->beginTransaction();
                }
                self::$transactionCount[$adapterHash]++;
                break;
            case 'commit':
                if(self::$transactionCount[$adapterHash] == 1) {
                    $adapter->getDriver()->getConnection()->commit();
                }
                self::$transactionCount[$adapterHash]--;
                break;
            case 'rollback':
                if(self::$transactionCount[$adapterHash] == 1) {
                    $adapter->getDriver()->getConnection()->rollback();
                }
                self::$transactionCount[$adapterHash]--;
                break;
        }
    }
    
    //getters/setters
    public function setDbAdapter(Adapter $adapter) {
        $this->setReadAdapter($adapter);
        $this->setWriteAdapter($adapter);
    }
    
    public function setReadAdapter(Adapter $readAdapter) {
        $this->readAdapter = $readAdapter;
    }
    
    public function getReadAdapter() {
        return $this->readAdapter;
    }
    
    public function getWriteAdapter() {
        return $this->writeAdapter;
    }

    public function setWriteAdapter(Adapter $writeAdapter) {
        $this->writeAdapter = $writeAdapter;
    }
}