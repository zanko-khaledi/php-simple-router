<?php

namespace App\Models;

use App\Connectors\Mongodb;
use App\Database;
use MongoDB\Client;

class Bar
{

    protected $table_name = 'test';

    protected ?Client $database = null;

    public function __construct()
    {
         $this->database = Database::connection(new Mongodb());
    }


    public function store(array $data)
    {
         $collection = (new Client())->zanko->test;

         $product = $collection->insertOne($data);

         if($product->getInsertedCount() > 0){
             return 'Product created';
         }
    }
}