<?php

namespace App\Connectors;

class Mongodb implements IConnector
{

    private ?\MongoDB\Client $connection = null;

    public function __construct()
    {
        $this->connection = new \MongoDB\Client('mongodb://localhost:27017');
    }

    public function getConnection(): ?\MongoDB\Client
    {
        return $this->connection;
    }
}