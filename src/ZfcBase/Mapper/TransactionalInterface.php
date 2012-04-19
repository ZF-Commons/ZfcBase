<?php

namespace ZfcBase\Mapper;

interface TransactionalInterface {
    public function beginTransaction();
    public function commit();
    public function rollback();
}