<?php

namespace ZfcBase\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\Common\Persistence\ObjectManager;
use ZfcBase\Repository\RepositoryInterface;
use ZfcBase\EventManager\EventProvider;

class DoctrineRepositoryProxy extends EventProvider implements RepositoryInterface
{
    protected $repository;

    protected $om;

    public function __construct(ObjectManager $om, ObjectRepository $repository)
    {
        $this->om = $om;
        $this->repository = $repository;
    }

    public function getObjectManager()
    {
        return $this->om;
    }

    public function getDoctrineRepository()
    {
        return $this->repository;
    }

    public function find($id)
    {
        $model = $this->getDoctrineRepository()->find($id);
        $this->events()->trigger(__FUNCTION__ . '.post', $this, array(
            'model' => $model,
            'om' => $this->getObjectManager())
        );
        return $model;
    }

    public function persist($model)
    {
        $this->events()->trigger(__FUNCTION__ . '.pre', $this, array(
                'model' => $model,
                'om' => $this->getObjectManager())
        );
        $this->getObjectManager()->persist($model);
        $this->events()->trigger(__FUNCTION__ . '.post', $this, array(
                'model' => $model,
                'om' => $this->getObjectManager())
        );
        return $this;
    }

    public function remove($model)
    {
        $this->events()->trigger(__FUNCTION__ . '.pre', $this, array(
                'model' => $model,
                'om' => $this->getObjectManager())
        );
        $this->getObjectManager()->remove($model);
        $this->events()->trigger(__FUNCTION__ . '.post', $this, array(
                'model' => $model,
                'om' => $this->getObjectManager())
        );
        return $this;
    }

    /**
     * Finds all objects in the repository.
     *
     * @return mixed The objects.
     */
    public function findAll()
    {
        $models = $this->getDoctrineRepository()->findAll();
        $this->events()->trigger(__FUNCTION__ . '.post', $this, array(
                'model' => $models,
                'om' => $this->getObjectManager())
        );
        return $models;
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
        $params = compact('criteria', 'orderBy', 'limit', 'offset');
        $params['om'] = $this->getObjectManager();
        $this->events()->trigger(__FUNCTION__ . '.pre', $this, $params);
        $models = $this->getDoctrineRepository()->findBy($criteria, $orderBy, $limit, $offset);
        $this->events()->trigger(__FUNCTION__ . '.post', $this, array(
                'model' => $models,
                'om' => $this->getObjectManager())
        );
        return $models;
    }

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array $criteria
     * @return object The object.
     */
    public function findOneBy(array $criteria)
    {
        $params = compact('criteria');
        $params['om'] = $this->getObjectManager();
        $this->events()->trigger(__FUNCTION__ . '.pre', $this, $params);
        $model = $this->getDoctrineRepository()->findOneBy($criteria);
        $this->events()->trigger(__FUNCTION__ . '.post', $this, array(
                'model' => $model,
                'om' => $this->getObjectManager())
        );
        return $model;
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
        return $this->getDoctrineRepository()->getClassName();
    }

    public function __call($method, $arguments)
    {
        return call_user_func_array(
            array(
                $this->getDoctrineRepository(),
                $method
            ),
            $arguments
        );
    }

}