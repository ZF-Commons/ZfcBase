<?php

namespace ZfcBase\Persistence;

use Zend\Stdlib\Options;

class DefaultObjectManagerOptions extends Options
{
    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var string
     */
    protected $primaryKey;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var array
     */
    protected $map;

    /**
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param string $primaryKey
     */
    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;
    }

    /**
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @param string $tableName
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param array $map
     */
    public function setMap(array $map)
    {
        $this->map = $map;
    }

    /**
     * @return array
     */
    public function getMap()
    {
        return $this->map;
    }


}