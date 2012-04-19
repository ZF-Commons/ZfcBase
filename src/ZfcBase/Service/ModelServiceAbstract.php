<?php

namespace ZfcBase\Service;

use Zend\Loader\LocatorAware,
    Zend\Di\Locator,
    Zend\EventManager\EventCollection,
    Zend\EventManager\EventManager,
    Zend\Paginator\Paginator,
    ZfcBase\Model\ModelAbstract,
    ZfcBase\Mapper\ModelMapperInterface,
    ZfcBase\Mapper\TransactionalInterface as Transactional,
    ZfcBase\Service\Exception\ModelNotFoundException;

class ModelServiceAbstract extends ServiceAbstract {
    protected $mapper;
    protected $modelPrototype;
    
    /**
     * @param array $data
     * @return array 
     */
    public function persist(array $data) {
        $mapper = $this->getMapper();
        try {
            if($mapper instanceof Transactional) {
                $mapper->beginTransaction();
            }

            $model = $this->createModelFromArray($data);

            $params = $this->triggerParamsMergeEvent('persist.pre', array(
                'data' => $data,
                'model' => $model,
            ));

            $ret = $mapper->persist($model);
            $params['model'] = $model;

            $params = $this->triggerParamsMergeEvent('persist.post', $params);

            if($mapper instanceof Transactional) {
                $mapper->commit();
            }
            
        } catch(\Exception $e) {
            if($mapper instanceof Transactional) {
                $mapper->rollback();
            }
            throw $e;
        }
        
        return $params;
    }
    
    public function get(array $filter, $exts = array()) {
        
        $model = $this->getModelPrototype();
        $modelClass = get_class($model);
        $result = $this->events()->trigger('get.load', $this, $filter, function($ret) use ($modelClass) {
            return $ret instanceof $modelClass;
        });
        $model = $result->last();
        if(!$model instanceof $modelClass) {
            throw new ModelNotFoundException("No model found filter: " . print_r($filter, true));
        }
        
        if($exts === true) {
            $this->triggerEvent('get.exts', array(
                'model' => $model,
            ));
        } else {
            foreach($exts as $ext) {
                $this->triggerEvent('get.ext.' . $ext, array(
                    'model' => $model,
                ));
            }
        }
        
        $this->triggerEvent('get.post', array(
            'model' => $model,
        ));
        
        return $model;
    }
    
    public function getPaginator($params = array()) {
        //TODO mapper should implement the interface
        $adapter = $this->getMapper()->getPaginatorAdapter($params);
        $paginator = new Paginator($adapter);
        return $paginator;
    }
    
    public function remove($id) {
        $mapper = $this->getMapper();
        $model = $mapper->findByPriKey($id);
        if(!$model) {
            throw new ModelNotFoundException("Model does not exist #$id");
        }

        try {
            if($mapper instanceof Transactional) {
                $mapper->beginTransaction();
            }
            $params = $this->triggerParamsMergeEvent('remove.pre', array(
                'model' => $model
            ));

            $ret = $mapper->remove($model);
            $params['model'] = $model;

            $params = $this->triggerParamsMergeEvent('remove.post', $params);

            if($mapper instanceof Transactional) {
                $mapper->commit();
            }
            
        } catch(\Exception $e) {
            if($mapper instanceof Transactional) {
                $mapper->rollback();
            }
            throw $e;
        }
        
        return $params;
    }
    
    protected function attachDefaultListeners() {
        parent::attachDefaultListeners();
        
        $events = $this->events();
        $mapper = $this->getMapper();
        
        //load by pri key
        $events->attach('get.load', function($e) use ($mapper){
            $priKey = $e->getParam('priKey');
            if(!$priKey) {
                return;
            }
            return $mapper->findByPriKey($priKey);
        });
    }
    
    protected function createModelFromArray(array $data) {
        $model = $this->getModelPrototype();
        $model->exchangeArray($data);
        return $model;
    }
    
    public function getModelPrototype() {
        return clone $this->modelPrototype;
    }

    public function setModelPrototype(ModelAbstract $modelPrototype) {
        $this->modelPrototype = $modelPrototype;
    }

    public function setMapper(ModelMapperInterface $mapper) {
        $this->mapper = $mapper;
    }
    
    public function getMapper() {
        //let mapper 'inherit' service's model prototype
        $this->mapper->setModelPrototype($this->getModelPrototype());
        return $this->mapper;
    }
    
    
}