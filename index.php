<?php


require_once __DIR__."/vendor/autoload.php";


$router = new \ZankoKhaledi\PhpSimpleRouter\Router();

$router->addRoute('GET','/',function (){
    echo "Hello World";
});