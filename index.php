<?php

declare(strict_types=1);


use ZankoKhaledi\PhpSimpleRouter\Request;
use ZankoKhaledi\PhpSimpleRouter\Router;

require __DIR__ . "/vendor/autoload.php";


class Test {
    public function index($args)
    {
        echo "Hello {$args[':name']}";
    }


    public function store(Request $request)
    {
        echo json_encode([
            'message' => "Hello {$request->post('name')}"
        ]);
    }
}

$router = new Router();

$router->addRoute('GET',"/products/id",'/products\/[0-9]+/',function (Request $request){
     echo $request->params()->id;
});

$router->addRoute('POST','/products',null,[Test::class,'store']);

$router->addRoute('PUT','/products/id','/products\/[0-9]+/',function (Request $request){
    echo json_encode([
        'message' => "Hello put request with name {$request->input('name')}"
    ]);
});



