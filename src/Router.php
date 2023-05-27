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
    public ?string $path = null;
    private ?string $uri = null;
    private array $validMethods = [
        'GET', 'POST', 'PUT', 'PATCH', 'DELETE'
    ];

    private ?string $serverMode;
    private ?Request $request = null;
    private ?array $routes = [];


    /**
     *
     * @throws ExceptionAlias
     */
    public function __construct(?string $method = null, ?string $path = null, callable|array $callback = null, array $middleware = [], string $pattern = "")
    {
        $this->serverMode = php_sapi_name();
        $this->uri = $this->serverMode === 'cli-server' ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : null;
        $this->method = $method ?? 'GET';
        $this->path = $path ?? '/';
        $this->callback = $callback;

        if (!is_null($method) && !is_null($path) && !is_null($callback)) {

            foreach ($this->routes as $index => $route) {
                if ($this->routes[$index]['path'] === $this->path && $this->routes[$index]['method'] === $method) {
                    throw new ExceptionAlias("route $path added before.");
                }
            }
            $route = $this->addRoute($method, $path, $callback);
            $pattern !== "" ? $route->middleware([...$middleware])->where($pattern) : $route->middleware([...$middleware]);
            $route->serve();
        }
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
     * @throws ExceptionAlias
     */
    public function addRoute(string $method, mixed $path, callable|array $callback): IRoute
    {
        if (!in_array($method, $this->validMethods)) {
            http_response_code(405);
            throw new \BadMethodCallException("$method not allowed.");
        }

        $this->path = $path;


        foreach ($this->routes as $index => $route) {
            if ($this->routes[$index]['path'] === $this->path && $this->routes[$index]['method'] === $method) {
                throw new ExceptionAlias("route $path added before.");
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
     * @throws ExceptionAlias
     */


    /**
     * @return void
     * @throws ExceptionAlias
     */
    public function serve(): void
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
        $this->args = [];
    }
}