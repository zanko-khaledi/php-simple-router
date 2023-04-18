# php-simple-router

this light weight router library can be useful for pure php web projects.


###installation 

    composer require zanko-khaledi/php-simple-router:^1.0.0

You can use this router like below  
    
    <?php

    use ZankoKhaledi\PhpSimpleRouter\Request;
    use ZankoKhaledi\PhpSimpleRouter\Router;
   
    require __DIR__ . "/vendor/autoload.php";

    $router = new Router();
    
    $router->addRoute('GET','/',function(Request $request){
       echo 'Hello World';
    });

Use Controller instead of callback functions 

    <?php

    use App\Controllers\FooController 
    use ZankoKhaledi\PhpSimpleRouter\Router;

    require __DIR__ . "/vendor/autoload.php";

    $router = new Router();
    $router->addRoute('POST','/foo',[FooController::class,'store']);

However you would be able to use dynamic route parameters with regex pattern

    <?php
    
    use ZankoKhaledi\PhpSimpleRouter\Request; 
    use ZankoKhaledi\PhpSimpleRouter\Router;

    require __DIR__ . "/vendor/autoload.php";

    $router = new Router();
    
    $router->addRoute('GET','/foo/id',function(Request $request){
       echo $request->params()->id;
    },'/foo\/[0-9]+/'); 
     
You can use prefix and group methods too
   
    <?php
    
    use ZankoKhaledi\PhpSimpleRouter\Request; 
    use ZankoKhaledi\PhpSimpleRouter\Router;
    use ZankoKhaledi\PhpSimpleRouter\Interfaces\IRoute;

    require __DIR__ . "/vendor/autoload.php";

    $router = new Router();
    
    $router->prefix('/foo')->addRoute('GET','/id',function(Request $reqeust){
       echo $request->params()->id;
    },'/foo\/[0-9]+/');
 
    $router->group('/bar',function(IRoute $router){
        $router->addRoute('GET','/id',function(Request $request){
            echo $request->params()->id;
        },'/foo\/[0-9]+/');
    });

    

   
   