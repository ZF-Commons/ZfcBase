<?php

namespace ZfcBase\Mapper;

use Zend\Db\Adapter\Adapter,
    Zend\Db\TableGateway\TableGatewayInterface,
    Zend\Db\TableGateway\TableGateway,
    ZfcBase\EventManager\EventProvider,
    Traversable;

abstract class DbMapperAbstract extends EventProvider
{
    /**
     * Database adapter for queries
     *
     * @var Adapter
     */
    protected $adapter;

    /**
     * The name of the table
     *
     * @var string
     */
    protected $tableName;

    /**
     * tableGateway 
     * 
     * @var TableGatewayInterface
     */
    protected $tableGateway;

    /**
     * Default database adapter
     *
     * @var Adapter
     */
    protected static $defaultAdapter;

    /**
     * Constructor
     *
     * @param Adapter $adapter
     *
     * @throws \Exception If there is no adapter defined
     *
     * @return void
     */
    final public function __construct(Adapter $adapter = null)
    {
        if (null === $adapter) {
            if (null === ($adapter = self::getDefaultAdapter())) {
                throw new \Exception('No database adapters defined');
            }
        }

        $this->adapter = $adapter;

        $this->init();
    }

    public function init() {}


    /**
     * Set the database adapter for queries 
     * 
     * @param Adapter $adapter 
     * @return DbMapperAbstract
     */
    public function setAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * Get the database adapter for queries
     *
     * @return Adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
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
     * @return DbMapperAbstract
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * Get tableGateway.
     *
     * @return TabeleGatewayInterface
     */
    public function getTableGateway()
    {
        if (null === $this->tableGateway && null !== $this->getTableName()) {
            $this->setTableGateway(new TableGateway($this->getTableName(), $this->getAdapter()));

        }
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
     * Set the default database adapter
     *
     * @param Adapter
     * @return void
     */
    public static function setDefaultAdapter(Adapter $adapter)
    {
        self::$defaultAdapter = $adapter;
    }

    /**
     * Get the default database adapter
     *
     * @return Adapter
     */
    public static function getDefaultAdapter()
    {
        return self::$defaultAdapter;
    }
}
