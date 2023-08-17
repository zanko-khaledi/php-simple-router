<?php


use Dotenv\Dotenv;
use ZankoKhaledi\PhpSimpleRouter\Interfaces\IMiddleware;
use ZankoKhaledi\PhpSimpleRouter\Interfaces\IRequest;
use ZankoKhaledi\PhpSimpleRouter\Request;
use ZankoKhaledi\PhpSimpleRouter\Router;


class AuthMiddleware implements IMiddleware{

    public function handle(IRequest $request,$next)
    {
        if($request->get('name') !== 'zanko' || $request->get('name') === null){
            $next($request);
        }
    }
}

class ZankoMiddleware implements IMiddleware{

    public function handle(IRequest $request, callable $next)
    {
        $next($request);
    }
}


Router::get('/', function (Request $request) {
    echo 'Root';
});


Router::get('/foo',[\App\Controllers\FooController::class,'index']);

Router::post('/foo',[\App\Controllers\FooController::class,'store']);

Router::get('/bar',function (Request $request){

    echo parse_url($_SERVER['HTTP_HOST'],PHP_URL_HOST);

});


Router::get('/test',function (){
    echo 'Test for localhost';
});


Router::group([
    'domain' => 'test.localhost',
    'middleware' => [
        AuthMiddleware::class
    ]
],function (){

    Router::get('/test',function (Request $request){
        echo "Hello Subdomain";
    });

    Router::get('/test/{file}',function (Request $request){
       echo $request->params()->file;
    })->middleware([
        ZankoMiddleware::class
    ]);

    Router::get('/zanko',function (Request $request){
        echo "Hello Zanko";
    });

});

