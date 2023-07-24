<?php


use ZankoKhaledi\PhpSimpleRouter\Request;
use ZankoKhaledi\PhpSimpleRouter\Router;
use ZankoKhaledi\PhpSimpleRouter\RouterCollection;

require __DIR__ . "/vendor/autoload.php";


Router::get('/route-not-found',function (Request $request){
    http_response_code(404);
    echo "<h4>Not found.</h4>";
});

RouterCollection::executeRoutesFrom("./routes/*.php");
