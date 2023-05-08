<?php
declare(strict_types=1);

namespace ZankoKhaledi\PhpSimpleRouter;

use ZankoKhaledi\PhpSimpleRouter\Interfaces\IMiddleware;
use ZankoKhaledi\PhpSimpleRouter\Interfaces\IRoute;
use ZankoKhaledi\PhpSimpleRouter\Traits\Testable;

final class Router implements IRoute
{

    use Testable;

    private ?string $prefix = null;
    private ?string $matchedUri = null;
    private ?string $pattern = null;
    private ?string $method = 'GET';
    private mixed $callback = null;
    private array $args = [];
    private ?string $path = null;
    private ?string $uri = null;
    private array $validMethods = [
        'GET', 'POST', 'PUT', 'PATCH', 'DELETE'
    ];

    private ?string $serverMode;
    private ?Request $request = null;
    private ?array $routes = [];

    /**
     *
     */
    public function __construct()
    {
        $this->serverMode = php_sapi_name();
        $this->uri = $this->serverMode === 'cli-server' ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : null;
    }

    /**
     * @param string $prefix
     * @param callable $callback
     * @return void
     */
    public function group(string $prefix, callable $callback)
    {
        $this->prefix = $prefix;
        $callback($this);
    }

    /**
     * @param string $prefix
     * @return IRoute
     */
    public function prefix(string $prefix): IRoute
    {
        $this->prefix = $prefix;
        return $this;
    }


    /**
     * @param array $middlewares
     * @return IRoute
     */
    public function middleware(array $middlewares): IRoute
    {
        foreach ($this->routes as $index => $route) {
            if ($this->routes[$index]['path'] === $this->path) {
                $this->routes[$index]['middlewares'] = [...$middlewares];
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
     * @throws \Exception
     */
    public function addRoute(string $method, mixed $path, callable|array $callback): IRoute
    {
        if (!in_array($method, $this->validMethods)) {
            http_response_code(405);
            throw new \BadMethodCallException("$method not allowed.");
        }

        $this->path = $this->prefix . $path;
        $this->method = $method;
        $this->callback = $callback;

        foreach ($this->routes as $index => $route) {
            if ($this->routes[$index]['path'] === $this->path && $this->routes[$index]['method'] === $method) {
                throw new \Exception("route $path added before.");
            }
        }

        $this->routes[] = [
            'method' => $method,
            'path' => $this->path,
            'callback' => $callback,
            'middlewares' => [],
            'valid' => true
        ];

        return $this;
    }


    /**
     * @return void
     * @throws \Exception
     */
    public function serve()
    {
        foreach ($this->routes as $index => $route) {
            if ($this->handleDynamicRouteParamsAndPath($route['path']) === $this->uri && $route['valid']) {
                $this->checkRequestMethod($route['method'], $route['callback'], $route['middlewares']);
            }
        }
    }


    /**
     * @param string $method
     * @param callable|array $callback
     * @param array $middlewares
     * @return void
     * @throws \Exception
     */
    private function checkRequestMethod(string $method, callable|array $callback, array $middlewares)
    {
        if ($method === $_SERVER['REQUEST_METHOD'] && in_array($method, $this->validMethods)) {
            $this->handleRoute($callback, $middlewares);
        }
    }


    /**
     * @param array|callable $callback
     * @param array $middlewares
     * @return void
     * @throws \Exception
     */
    private function handleRoute(array|callable $callback, array $middlewares)
    {
        $this->request = new Request($this->args);

        foreach ($middlewares as $middleware) {
            $instance = (new $middleware);
            if ($instance instanceof IMiddleware) {
                $instance->handle($this->request);
            } else {
                throw new \Exception("$middleware must be type of IMiddleware interface.");
            }
        }

        $this->handleCallback($callback);
    }


    /**
     * @param callable|array $callback
     * @return void
     */
    private function handleCallback(callable|array $callback)
    {
        is_array($callback) && count($callback) === 2 ?
            call_user_func_array([new $callback[0], $callback[1]], [$this->request]) :
            $callback($this->request);
    }


    /**
     * @param string $route
     * @return string
     */
    private function handleDynamicRouteParamsAndPath(string $route): string
    {
        $pattern = "/{(.*?)}/";
        preg_match_all($pattern, $route, $matches);

        $uriArray = explode('/', $this->uri);
        $pathArray = explode('/', $route);
        $uriDiff = array_diff($uriArray, $pathArray);
        $path = "";
        if (count($matches[1]) === count($uriDiff)) {
            $this->args = [...array_combine($matches[1], $uriDiff)];

            $path = $route;
            $path = preg_replace("$pattern", "%s", $path);
            $path = sprintf($path, ...array_values($this->args));
        }


        return $path;
    }


    public function __destruct()
    {
        $this->prefix = null;
        $this->pattern = null;
        $this->path = null;
        $this->args = [];
    }
}