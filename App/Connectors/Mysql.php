<?php

namespace App\Connectors;

use PDO;

class Mysql implements IConnector
{
    private ?PDO $connection = null;

    public function __construct()
    {
        $config = database()['mysql'];
        $dsn = sprintf("mysql:host=%s;dbname=%s;",$config['host'],$config['dbname']);
        $this->connection = new PDO($dsn,$config['username'],$config['password']);
    }


    public function getConnection(): ?PDO
    {
        return $this->connection;
    }
}