<?php

namespace ZfcBase\Mapper;

use Zend\Db\TableGateway\TableGatewayInterface,
    Zend\Db\TableGateway\TableGateway,
    ZfcBase\EventManager\EventProvider,
    Traversable,
    InvalidArgumentException;

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
            
            throw new InvalidArgumentException("Can not convert '$key' key value to string");
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
            return $obj->format('Y-m-d\TH:i:sP');
        }
        
        throw new InvalidArgumentException("Can not convert object '" . get_class($obj) . "' to string");
    }
}
