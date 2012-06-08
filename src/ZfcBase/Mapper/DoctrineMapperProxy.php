<?php

namespace ZfcBase\Mapper;

use Doctrine\Common\Persistence\ObjectManager;
use ZfcBase\EventManager\EventProvider;

class DoctrineMapperProxy extends EventProvider implements DataMapperInterface
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var string
     */
    protected $className;

    public function __construct(ObjectManager $om, $className)
    {
        $this->om = $om;
        $this->className = $className;
    }

    public function persist($model)
    {
        $om = $this->getObjectManager();
        $this->events()->trigger(__FUNCTION__ . '.pre', $this, array('model' => $model, 'om' => $om));
        $om->persist($model);
        $this->events()->trigger(__FUNCTION__ . '.post', $this, array('model' => $model, 'om' => $om));
        $om->flush();
    }

    public function find($id)
    {
        $om = $this->getObjectManager();
        $model = $om->getRepository($this->getClassName())->find($id);
        $this->events()->trigger(__FUNCTION__, $this, array('model' => $model, 'om' => $om));
        return $model;
    }

    public function remove($model)
    {
        $om = $this->getObjectManager();
        $this->events()->trigger(__FUNCTION__ . '.pre', $this, array('user' => $model, 'om' => $om));
        $om->remove($model);
        $this->events()->trigger(__FUNCTION__ . '.post', $this, array('user' => $model, 'om' => $om));
        $om->flush();
    }
    public function getPaginatorAdapter(array $params)
    {
        // TODO: Implement getPaginatorAdapter() method.
    }

    /**
     * Returns the class name of the object mapped by the data mapper
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->om;
    }

    public function setObjectManager(ObjectManager $om)
    {
        $this->om = $om;
        return $this;
    }

}