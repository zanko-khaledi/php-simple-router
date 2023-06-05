<?php
declare(strict_types=1);

namespace ZankoKhaledi\PhpSimpleRouter;

use Exception as ExceptionAlias;
use ZankoKhaledi\PhpSimpleRouter\Interfaces\IMiddleware;
use ZankoKhaledi\PhpSimpleRouter\Interfaces\IRoute;
use ZankoKhaledi\PhpSimpleRouter\Traits\Testable;

final class Router implements IRoute
{

    use Testable;

    public ?string $method = 'GET';
    private mixed $callback = null;
    private array $args = [];
    public ?string $path = '/';
    private ?string $uri = '/';
    private array $validMethods = [
        'GET', 'POST', 'PUT', 'PATCH', 'DELETE'
    ];

    private ?string $serverMode;
    private ?Request $request = null;
    private static ?Router $instance = null;
    private ?string $prefix = null;
    private ?array $routes = [];
    private array $middlewares = [];


    /**
     *
     * @throws ExceptionAlias
     */
    private function __construct()
    {
        $this->serverMode = php_sapi_name();
        $this->uri = $this->serverMode === 'cli-server' ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : null;
    }

    /**
     * @return void
     */
    private function __clone()
    {

    }

    /**
     * create a singleton instance pattern to instantiate only one object from this class
     * @return static|null
     */
    private static function getInstance(): ?static
    {
        if (static::$instance === null || is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @param array $attributes
     * @param callable $callback
     * @return void
     */
    public static function group(array $attributes, callable $callback): void
    {
        if (array_key_exists('prefix', $attributes)) {
            static::getInstance()->prefix = $attributes['prefix'];
        }
        if (array_key_exists('middleware', $attributes)) {
            static::getInstance()->middlewares = is_array($attributes['middleware']) && count($attributes['middleware']) ? [...$attributes['middleware']] : $attributes['middleware'];
        }

        call_user_func($callback);

        static::getInstance()->prefix = null;
        static::getInstance()->middlewares = [];
    }


    /**
     * @param string $path
     * @param callable|array $callback
     * @return IRoute
     * @throws ExceptionAlias
     */
    public static function get(string $path, callable|array $callback): IRoute
    {
        return static::getInstance()->addRoute('GET', $path, $callback);
    }

    /**
     * @param string $path
     * @param callable|array $callback
     * @return IRoute
     * @throws ExceptionAlias
     */
    public static function post(string $path, callable|array $callback): IRoute
    {
        return static::getInstance()->addRoute('POST', $path, $callback);
    }

    /**
     * @param string $path
     * @param callable|array $callback
     * @return IRoute
     * @throws ExceptionAlias
     */
    public static function put(string $path, callable|array $callback): IRoute
    {
        return static::getInstance()->addRoute('PUT', $path, $callback);
    }

    /**
     * @param string $path
     * @param callable|array $callback
     * @return IRoute
     * @throws ExceptionAlias
     */
    public static function patch(string $path, callable|array $callback): IRoute
    {
        return static::getInstance()->addRoute('PATCH', $path, $callback);
    }

    /**
     * @param string $path
     * @param callable|array $callback
     * @return IRoute
     * @throws ExceptionAlias
     */
    public static function delete(string $path, callable|array $callback): IRoute
    {
        return static::getInstance()->addRoute('DELETE', $path, $callback);
    }


    /**
     * @return void
     * @throws ExceptionAlias
     */
    public static function executeRoutes(): void
    {
        static::getInstance()->serve();
    }


    /**
     * @param array $middlewares
     * @return IRoute
     */
    public function middleware(array $middlewares): IRoute
    {
        foreach ($this->routes as $index => $route) {
            if ($this->routes[$index]['path'] === $this->path) {
                $this->routes[$index]['middlewares'] = [...$middlewares, ...$this->middlewares];
            }
        }

        return $this;
    }

    /**
     * @param string $pattern
     * @return IRoute
     */
    public function where(string $pattern): IRoute
    {
        preg_match($pattern, $this->uri, $matches);

        foreach ($this->routes as $index => $route) {
            if ($route['path'] === $this->path) {
                $this->routes[$index]['valid'] = count($matches) > 0;
            }
        }

        return $this;
    }


    /**
     * @param string $method
     * @param mixed $path
     * @param callable|array $callback
     * @return IRoute
     * @throws ExceptionAlias
     */
    private function addRoute(string $method, mixed $path, callable|array $callback): IRoute
    {
        if (!in_array($method, $this->validMethods)) {
            http_response_code(405);
            throw new \BadMethodCallException("$method not allowed.");
        }

        $this->path = $this->prefix !== null ? $this->prefix . $path : $path;

        foreach ($this->routes as $index => $route) {
            if ($this->routes[$index]['path'] === $this->path && $this->routes[$index]['method'] === $method) {
                throw new ExceptionAlias("route $path added before.");
            }
        }

        $this->routes[] = [
            'method' => $method,
            'path' => $this->path,
            'callback' => $callback,
            'middlewares' => [...$this->middlewares],
            'valid' => true
        ];

        return $this;
    }

    /**
     * @return void
     * @throws ExceptionAlias
     */
    private function serve(): void
    {
        foreach ($this->routes as $index => $route) {
            if ($this->handleDynamicRouteParamsAndPath($route['path'],$this->uri) && $route['valid']) {
                $this->checkRequestMethod($route['method'], $route['callback'], $route['middlewares']);
                return;
            }
        }

        header('Location:/route-not-found');
        exit();
    }


    /**
     * @param string $method
     * @param callable|array $callback
     * @param array $middlewares
     * @return void
     * @throws ExceptionAlias
     */
    private function checkRequestMethod(string $method, callable|array $callback, array $middlewares): void
    {
        if ($method === $_SERVER['REQUEST_METHOD'] && in_array($method, $this->validMethods)) {
            $this->handleRoute($callback, $middlewares);
        }
    }


    /**
     * @param array|callable $callback
     * @param array $middlewares
     * @return void
     * @throws ExceptionAlias
     */
    private function handleRoute(array|callable $callback, array $middlewares): void
    {
        $this->request = new Request($this->args);

        foreach ($middlewares as $middleware) {
            $instance = (new $middleware);
            if ($instance instanceof IMiddleware) {
                $instance->handle($this->request);
            } else {
                throw new ExceptionAlias("$middleware must be type of IMiddleware interface.");
            }
        }

        $this->handleCallback($callback);
    }


    /**
     * @param callable|array $callback
     * @return void
     */
    private function handleCallback(callable|array $callback): void
    {
        is_array($callback) && count($callback) === 2 ?
            call_user_func_array([new $callback[0], $callback[1]], [$this->request]) :
            call_user_func($callback, $this->request);
    }


    /**
     * @param string $route
     * @return bool
     */
    private function handleDynamicRouteParamsAndPath(string $route,string $uri): bool
    {
        $pattern = "/{(.*?)}/";
        preg_match_all($pattern, $route, $matches);

        $uriArray = explode('/', $this->uri);
        $pathArray = explode('/', $route);
        $uriDiff = array_diff($uriArray, $pathArray);
        $path = "";
        if (count($matches[1]) === count($uriDiff)) {
            $this->args = [...array_combine($matches[1], $uriDiff)];
            $path = sprintf(preg_replace("$pattern", "%s", $route), ...array_values($this->args));
        }

        return $path === $uri;
    }

    public function __destruct()
    {
        $this->args = [];
    }
}