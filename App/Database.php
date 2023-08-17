<?php

namespace App;

use App\Connectors\IConnector;
use PDO;

class Database
{
    private mixed $connection = null;

    private static $instance = null;

    private function __construct()
    {

    }

    public function __clone()
    {

    }

    private static function getInstance():?static
    {
        if(static::$instance === null){
            static::$instance = new static();
        }
        return static::$instance;
    }

    public static function connection(IConnector $connector)
    {
        return static::getInstance()->connection = $connector->getConnection();
    }
}