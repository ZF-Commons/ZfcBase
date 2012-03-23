<?php

namespace ZfcBase\Mapper;

interface Transactional {
    public function beginTransaction();
    public function commit();
    public function rollback();
}