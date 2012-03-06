<?php

namespace ZfcBase\Mapper;

use Zend\Db\TableGateway\TableGatewayInterface,
    Zend\Db\TableGateway\TableGateway,
    ZfcBase\EventManager\EventProvider,
    Traversable;

abstract class DbMapperAbstract extends EventProvider
{
    /**
     * tableGateway 
     * 
     * @var TableGatewayInterface
     */
    protected $tableGateway;

    /**
     * Constructor
     *
     * @param TableGatewayInterface $tableGateway
     *
     * @throws \Exception If there is no adapter defined
     *
     * @return void
     */
    public function __construct(TableGatewayInterface $tableGateway = null)
    {
        if (null !== $tableGateway) {
            $this->setTableGateway($tableGateway);
        }
    }

    /**
     * Get tableGateway.
     *
     * @return TabeleGatewayInterface
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
}
