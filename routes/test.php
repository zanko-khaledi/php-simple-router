<?php


use ZankoKhaledi\PhpSimpleRouter\Request;
use ZankoKhaledi\PhpSimpleRouter\Router;

ob_start();

Router::get('/', function (Request $request) {
    echo 'Root';
});


Router::get('/foo',[\App\Controllers\FooController::class,'index']);

Router::post('/foo',[\App\Controllers\FooController::class,'store']);


Router::get('/buffer',function (Request $request){

    echo  "Hello Zanko";

    ob_flush();
    flush();

    sleep(5);

    echo "Hello Buffer";

    ob_flush();

    flush();
});
