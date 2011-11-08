<?php

namespace EdpCommon\Mapper;

use Zend\Db\Adapter\AbstractAdapter,
    EdpCommon\EventManager\EventProvider,
    Traversable;

abstract class DbMapperAbstract extends EventProvider
{
    /**
     * Database adapter for read queries
     *
     * @var Zend\Db\Adapter\AbstractAdapter
     */
    protected $readAdapter;

    /**
     * Database adapter for write queries
     *
     * @var Zend\Db\Adapter\AbstractAdapter
     */
    protected $writeAdapter;

    /**
     * The name of the table 
     * 
     * @var string
     */
    protected $tableName;

    /**
     * Default database adapter
     *
     * @var Zend\Db\Adapter\AbstractAdapter
     */
    protected static $defaultAdapter;

    /**
     * Constructor
     *
     * @param Zend\Db\Adapter\AbstractAdapter $writeAdapter
     * @param Zend\Db\Adapter\AbstractAdapter $readAdapter
     *
     * @throws \Exception If there is no adapter defined
     *
     * @return void
     */
    final public function __construct(AbstractAdapter $writeAdapter = null, AbstractAdapter $readAdapter = null)
    {
        if (null === $writeAdapter) {
            if (null === ($writeAdapter = self::getDefaultAdapter())) {
                throw new \Exception('No database adapters defined');
            }
        }

        if (null === $readAdapter) {
            $readAdapter = $writeAdapter;
        }

        $this->readAdapter = $readAdapter;
        $this->writeAdapter = $writeAdapter;

        $this->init();
    }

    public function init() {}

    /**
     * Get the database adapter for read queries
     *
     * @return Zend\Db\Adapter\AbstractAdapter
     */
    public function getReadAdapter()
    {
        return $this->readAdapter;
    }

    /**
     * Get the database adapter for write queries
     *
     * @return Zend\Db\Adapter\AbstractAdapter
     */
    public function getWriteAdapter()
    {
        return $this->writeAdapter;
    }

    /**
     * Get tableName.
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }
 
    /**
     * Set tableName.
     *
     * @param $tableName the value to be set
     * @return Edp\Common\DbMapper
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * Set the default database adapter
     *
     * @param Zend\Db\Adapter\AbstractAdapter
     * @return void
     */
    public static function setDefaultAdapter(AbstractAdapter $adapter)
    {
        self::$defaultAdapter = $adapter;
    }

    /**
     * Get the default database adapter
     *
     * @return Zend\Db\Adapter\AbstractAdapter
     */
    public static function getDefaultAdapter()
    {
        return self::$defaultAdapter;
    }
}
