<?php

namespace ZfcBase\Repository;

use ZfcBase\EventManager\EventProvider;
use ZfcBase\Mapper\AbstractDbMapper;

abstract class AbstractDbRepository extends EventProvider implements RepositoryInterface
{
    /**
     * @var AbstractDbMapper
     */
    protected $mapper;

    /**
     * Set mapper
     *
     * @param AbstractDbMapper $mapper
     */
    public function setMapper(AbstractDbMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * Get mapper
     *
     * @return AbstractDbMapper
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->getMapper()->getTableName();
    }

    /**
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->getMapper()->getPrimaryKey();
    }

    public function find($id)
    {
        $rowset = $this->getTableGateway()->select(array($this->getPrimaryKey() => $id));
        $row = $rowset->current();
        $user = $this->fromRow($row);
        $this->events()->trigger(__FUNCTION__ . '.post', $this, array('user' => $user, 'row' => $row));
        return $user;
    }

    protected function fromRow($row)
    {
        if (!$row) return false;
        $userModelClass = $this->getClassName();
        $user = $userModelClass::fromArray($row->getArrayCopy());
        $user->setLastLogin(DateTime::createFromFormat('Y-m-d H:i:s', $row['last_login']));
        $user->setRegisterTime(DateTime::createFromFormat('Y-m-d H:i:s', $row['register_time']));
        return $user;
    }
}