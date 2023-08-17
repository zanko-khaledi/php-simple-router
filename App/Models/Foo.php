<?php

namespace App\Models;

use App\Connectors\Mysql;
use App\Database;
use ZankoKhaledi\PhpSimpleRouter\HTTPResponse;

class Foo
{

    protected string $table_name = 'test';

    protected ?\PDO $database = null;

    public function __construct()
    {
        $this->database = Database::connection(new Mysql());
    }

    public function all()
    {
        $db = $this->database;

        $stmt = $db->prepare("SELECT * FROM {$this->table_name}");

        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    public function create(array $data)
    {
        $db = $this->database;

        try {
          $db->beginTransaction();

          $stmt = $db->prepare("INSERT INTO {$this->table_name}(name) VALUES(:name)");

          $stmt->execute([
              ':name' => $data['name']
          ]);

          $db->commit();
        }catch (\Exception $exception){
          $db->rollBack();

          return $exception->getMessage();
        }

        if($stmt->rowCount()){
            return "user created.";
        }
    }
}