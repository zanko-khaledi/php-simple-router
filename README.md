# php-simple-router

this light weight router library can be useful for small php web projects or building you'r api.

## installation

```bash
composer require zanko-khaledi/php-simple-router:^1.0.0
```

## Usage

You can use this router like below

   ```php
    <?php

    use ZankoKhaledi\PhpSimpleRouter\Request;
    use ZankoKhaledi\PhpSimpleRouter\Router;
   
    require __DIR__ . "/vendor/autoload.php";

    $router = new Router();
    
    $router->addRoute('GET','/',function(Request $request){
       echo 'Hello World';
    });

    $router->serve();
   ```

Use Controller instead of callback functions

  ```php
   <?php

    use App\Controllers\FooController 
    use ZankoKhaledi\PhpSimpleRouter\Router;

    require __DIR__ . "/vendor/autoload.php";

    $router = new Router();
    
    $router->addRoute('POST','/foo',[FooController::class,'store']);
    
    $router->serve();
  ```

However you would be able to use dynamic route parameters

   ```php
    <?php
    
    use ZankoKhaledi\PhpSimpleRouter\Request; 
    use ZankoKhaledi\PhpSimpleRouter\Router;

    require __DIR__ . "/vendor/autoload.php";

    $router = new Router();
    
    $router->addRoute('GET','/foo/{id}',function(Request $request){
       echo $request->params()->id;
    });
    
    $router->addRoute('GET','/bar/{id}',function (Request $request){
       echo $request->params()->id;
    })->where('/bar\/[0-9]+/'); // regex validation for route params with where method

    $router->serve();
   ```

Add router collection for modular routing

   ```php
   <?php
    
    use ZankoKhaledi\PhpSimpleRouter\RouterCollection;

    require __DIR__."/vendor/autoload.php"; 

    $routeCollection = new RouterCollection();

    try {

      $routeCollection->loadRoutesFrom("./routes/*.php");

    } catch (Exception $e) {
       echo $e->getMessage();
    }
   ```

Or if you want add routes separately you can do like this:

   ```php
      $routeCollection->loadRouteFrom("./routes/test.php");
   ```

## Request methods

You can use only this request methods to handle you're api

 ```bash 
    GET,POST,PUT,PATCH,DELETE
 ``` 

## Middleware

Create a class for example AuthMiddleware that implements IMiddleware contract

```php
<?php

 use ZankoKhaledi\PhpSimpleRouter\Interfaces\IMiddleware;
 use ZankoKhaledi\PhpSimpleRouter\Interfaces\IRequest;
  
 class AuthMiddleware implements IMiddleware
 {
   public function handle(IRequest $request)
   {
     if(!isset($_SESSION['admin']) && $_SESSION['admin'] !== 'zanko'){
           header("Location:/");
           exit();
     }
   }
 }
```

After middleware has been created you should register it on you're router

```php
<?php

  use App\Middlewares\AuthMiddleware;

  require __DIR__ . "/vendor/autoload.php";
  
  $router = new \ZankoKhaledi\PhpSimpleRouter\Router();
  
  $router->addRoute('GET','/',function (\ZankoKhaledi\PhpSimpleRouter\Request $request){
      echo "Root path";
  });
  
  $router->addRoute('GET','/foo',function (\ZankoKhaledi\PhpSimpleRouter\Request $request){
     echo "Hello foo router";
  })->middleware([AuthMiddleware::class]);

  $router->serve();
```

## Testing

   ```php
   <?php
        
        declare(strict_types=1);
        
        use PHPUnit\Framework\TestCase;
        use ZankoKhaledi\PhpSimpleRouter\Router;
        
        class Test extends TestCase
        {
        
            protected string $baseUri = 'http://localhost:8000';
        
            public function test_get_route()
            {
                $request = Router::assertRequest($this->baseUri);
                $response = $request->assertGet('/foo',[]);
        
                $this->assertEquals(200,$response->status());
                $this->assertJson($response->json());
                $this->assertSame(json_encode([
                    'name' => 'Zanko'
                ]),$response->body());
            }
        
        
            public function test_zanko_post()
            {
                $request = Router::assertRequest($this->baseUri);
                $response = $request->assertPost('/foo',[
                   'form_params' => [
                       'name' => 'Teddy'
                   ]
                ]);
        
                $this->assertEquals(201,$response->status());
                $this->assertJson($response->json());
                $this->assertSame(json_encode([
                    'name' => 'Foo'
                ]),$response->json());
            }
        }   
       
   ```

You can test you're api like code block above. some test api :

```php
  <?php
    
    use ZankoKhaledi\PhpSimpleRouter\Router;
    
    $baseUri = 'http://localhost:8000';
    $request = Router::assertRequest($baseUri) // config base uri for sending requests to server 
    
    $request->assertGet($route,[]); // route like /foo
 
    $request->assertPost($route,[]);

    $request->assertPut($route,[]);

    $request->assertPatch($route,[]);

    $request->assertDelete($route,[]);
```        

if you familiar to PHPUnit test framework and Guzzle/Http library you could test you're api without Router test api by
default.

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License

[MIT](https://choosealicense.com/licenses/mit/)
