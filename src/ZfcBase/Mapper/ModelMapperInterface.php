<?php
namespace ZfcBase\Mapper;

use ZfcBase\Model\ModelAbstract;

interface ModelMapperInterface {
    public function findByPriKey($key);
    public function persist(ModelAbstract $model);
    public function remove(ModelAbstract $model);
    public function getPaginatorAdapter(array $params);
    public function getModelPrototype();
    public function setModelPrototype(ModelAbstract $modelPrototype);
}