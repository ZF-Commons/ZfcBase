<?php

namespace EdpCommon\Mapper;

use Zend\Db\Adapter\AbstractAdapter,
    Zend\EventManager\EventCollection,
    Zend\EventManager\EventManager,
    Traversable;

abstract class DbMapperAbstract
{
    /**
     * @var EventCollection
     */
    protected $events;

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

    /**
     * Set the event manager instance used by this context
     * 
     * @param  EventCollection $events 
     * @return mixed
     */
    public function setEventManager(EventCollection $events)
    {
        $this->events = $events;
        return $this;
    }

    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     * 
     * @return EventCollection
     */
    public function events()
    {
        if (!$this->events instanceof EventCollection) {
            $identifiers = array(__CLASS__, get_class($this));
            if (isset($this->eventIdentifier)) {
                if ((is_string($this->eventIdentifier))
                    || (is_array($this->eventIdentifier))
                    || ($this->eventIdentifier instanceof Traversable)
                ) {
                    $identifiers = array_unique($identifiers + (array) $this->eventIdentifier);
                } elseif (is_object($this->eventIdentifier)) {
                    $identifiers[] = $this->eventIdentifier;
                }
                // silently ignore invalid eventIdentifier types
            }
            $this->setEventManager(new EventManager($identifiers));
        }
        return $this->events;
    }
}
