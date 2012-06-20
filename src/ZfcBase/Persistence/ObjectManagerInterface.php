<?php

namespace ZfcBase\Persistence;

interface ObjectManagerInterface
{
    public function persist($object);

    public function remove($object);

    public function flush($object = null, array $options = array());
}