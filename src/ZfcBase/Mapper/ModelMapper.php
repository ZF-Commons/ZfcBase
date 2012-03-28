<?php
namespace ZfcBase\Mapper;

use ZfcBase\Model\ModelAbstract;

interface ModelMapper {
    public function findByPriKey($key);
    public function persist(ModelAbstract $model);
    public function remove(ModelAbstract $model);
    public function getPaginatorAdapter(array $params);
}