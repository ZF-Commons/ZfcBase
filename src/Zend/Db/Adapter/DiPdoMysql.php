<?php

namespace Zend\Db\Adapter;

use PDO;

class DiPdoMysql extends PdoMysql
{
    public function __construct(PDO $pdo, $config)
    {
        $this->_connection = $pdo;
        $config = array_replace_recursive($config, array('dbname' => '', 'username' => '', 'password' => ''));
        parent::__construct($config);
    }
}
