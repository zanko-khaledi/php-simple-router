# php-simple-router

this light weight router library can be useful for small php web projects or building you'r api.

## installation

```bash
$ composer require zanko-khaledi/php-simple-router:^1.0.0
```

## Usage

You can use this router like below

   ```php
    <?php

    use ZankoKhaledi\PhpSimpleRouter\Request;
    use ZankoKhaledi\PhpSimpleRouter\Router;
   
    require __DIR__ . "/vendor/autoload.php";

    Router::get('/',function (Request $request){
        echo "Hello World";
    });

    Router::get('/foo',function (Request $request){
        echo "foo route";
    });

    Router::executeRoutes();
   ```

Use Controller instead of callback functions

  ```php
   <?php

    use App\Controllers\FooController; 
    use ZankoKhaledi\PhpSimpleRouter\Router;

    require __DIR__ . "/vendor/autoload.php";
    
    
    Router::get('/foo/create',[FooController::class,'create']);
    
    Router::post('/foo',[FooController::class,'store']);

    Router::executeRoutes();
  ```

However you would be able to use dynamic route parameters

   ```php
    <?php
    
    use ZankoKhaledi\PhpSimpleRouter\Request; 
    use ZankoKhaledi\PhpSimpleRouter\Router;

    require __DIR__ . "/vendor/autoload.php";

    Router::get('/bar/{id}',function (Request $request){
       echo $request->params()->id;
    });
    
    Router::get('/foo/{file}',function (Request $request){
       echo $request->params()->file;
    })->where('/foo\/[a-z]+/');

    Router::executeRoutes();
   ```
## Router Collection
Add router collection for modular routing

   ```php
   <?php
    
    use ZankoKhaledi\PhpSimpleRouter\RouterCollection;

    require __DIR__."/vendor/autoload.php"; 

    
    RouterCollection::executeRoutesFrom('./Modules/*/routes/*.php');
     
   ```
<header style="text-align: center;color: darkred">
  <h3>Notice</h3>
</header>
If you use <code>RouterCollection</code> class for load your route files , you have to avoid call <code>Router::executeRoutes()</code>
in your route files because this method calling once you invoke <code>executeRoutesFrom()</code> method from <code>RouterCollection</code>
so just do like this : 

 ```php
   <?php
   
    // Modules/Product/routes/product.php
    
    use ZankoKhaledi\PhpSimpleRouter\Router;
    use ZankoKhaledi\PhpSimpleRouter\Request;
    use 
    
    Router::get('/products',function (Request $request){
       // your code
    });
     
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
   public function handle(IRequest $request,Callable $next)
   {
     if(!isset($_SESSION['admin']) && $_SESSION['admin'] !== 'zanko'){
           header("Location:/");
           exit();
     }
     $next($request);
   }
 }
```

After middleware has been created you should register it on you're router

```php
<?php

  use App\Middlewares\AuthMiddleware;
  use ZankoKhaledi\PhpSimpleRouter\Router;
  use ZankoKhaledi\PhpSimpleRouter\Request;

  require __DIR__ . "/vendor/autoload.php";
  
  Router::get('/foo',function (Request $request){
     // your code
  })->middleware([AuthMiddleware::class]); 

  Router::executeRoutes();
```

## Group 

you can use group route binding

```php
<?php
  use ZankoKhaledi\PhpSimpleRouter\Request; 
  use ZankoKhaledi\PhpSimpleRouter\Router;
  use ZankoKhaledi\PhpSimpleRouter\Interfaces\IRoute;

  require __DIR__ . "/vendor/autoload.php";

  Router::group(['prefix' => '/foo'],function (Request $request){
     Router::get('/bar',function (Request $request){
        // your code
     });
  });

  Router::executeRoutes();
```
Also you would be able to bind middlewares to group method

```php
<?php
  use ZankoKhaledi\PhpSimpleRouter\Request; 
  use ZankoKhaledi\PhpSimpleRouter\Router;
  use ZankoKhaledi\PhpSimpleRouter\Interfaces\IRoute;
  use App\Middelwares\AuthMiddleware;
  use App\Controllers\FooController;

  require __DIR__ . "/vendor/autoload.php";

 
  
  Router::group(['prefix' => '/bar','middleware' => [AuthMiddleware::class]],function (IRoute $router){
  
      Router::get('/foo/{id}',function (Request $request){
         echo $request->params()->id;
      });
      
      Router::post('/foo',[FooController::class,'store']);
      
  }); 

  Router::executeRoutes();
```

You can also use subdomains in <code>group</code> method like block code below
```php
  use ZankoKhaledi\PhpSimpleRouter\Request; 
  use ZankoKhaledi\PhpSimpleRouter\Router;
  use ZankoKhaledi\PhpSimpleRouter\Interfaces\IRoute;
  use App\Middelwares\AuthMiddleware;

  require __DIR__ . "/vendor/autoload.php";


  Router::group([
  'domain' => 'example.local'
  ,'prefix' => '/bar'
  ,'middleware' => [AuthMiddleware::class]]
  ,function (IRoute $router){
     // code  
  }); 

  Router::executeRoutes();

```
By default you're host name is localhost you would be able to change it in <code>.env</code>
file on root of you're project for example 
```.dotenv

HOSTNAME = mysite.local

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
        
        
            public function test_foo_post_route()
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
