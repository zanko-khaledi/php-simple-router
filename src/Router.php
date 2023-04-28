<?php
declare(strict_types=1);

namespace ZankoKhaledi\PhpSimpleRouter;


use ZankoKhaledi\PhpSimpleRouter\Abstracts\BaseRoute;
use ZankoKhaledi\PhpSimpleRouter\Interfaces\IRequest;
use ZankoKhaledi\PhpSimpleRouter\Interfaces\IRoute;
use ZankoKhaledi\PhpSimpleRouter\Traits\Testable;


final class Router  implements IRoute
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
    private ?array $middlewares = [];
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
        $this->middlewares = [...$middlewares];
        return $this;
    }


    /**
     * @param string|null $pattern
     * @return IRoute
     */
    public function where(string $pattern = null): IRoute
    {
        $this->pattern = $pattern;
        if ($this->pattern !== null) {
            preg_match($this->pattern, $this->uri, $matches);
            if (count($matches) > 0) {
                $this->matchedUri = '/' . $matches[0];
            } else {
                $this->matchedUri = null;
            }
        }

        return $this;
    }

    /**
     * @param string $method
     * @param mixed $path
     * @param callable|array $callback
     * @return IRoute
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


        return $this;
    }

    /**
     * @return void
     */
    public function serve()
    {
        $this->checkRequestMethod($this->method, $this->path, $this->callback);
    }

    /**
     * @param string $method
     * @param string $path
     * @param callable|array $callback
     * @return void
     */
    private function checkRequestMethod(string $method, string $path, callable|array $callback)
    {
        if ($method === $_SERVER['REQUEST_METHOD'] && in_array($method, $this->validMethods)) {
            $this->handleRoute($path, $callback);
        }
    }


    /**
     * @param string $path
     * @param array|callable $callback
     * @return void
     */
    private function handleRoute(string $path, array|callable $callback)
    {
        if ($this->matchedUri !== null) {
            if ($this->uri === $this->matchedUri && $this->checkPath()) {
                $this->determineArguments();
                $this->handleCallbacks($callback);
            }
        } else {
            if ($path === $this->uri) {
                $this->handleCallbacks($callback);
            }
        }
    }

    /**
     * @param callable|array $callback
     * @return void
     */
    private function handleCallbacks(callable|array $callback)
    {
        $this->request = new Request($this->args);

        foreach ($this->middlewares as $middleware){
            (new $middleware)->handle($this->request);
        }

        if (is_callable($callback)) {
            $callback($this->request);
        }
        if (is_array($callback)) {
            call_user_func_array([new $callback[0], $callback[1]], [$this->request]);
        }
    }

    /**
     * @return void
     */
    private function determineArguments()
    {
        $uriArray = explode("/", $this->uri);
        $pathArray = explode("/", $this->path);
        $uriDiff = array_keys(array_flip(array_diff($uriArray, $pathArray)));
        $pathDiff = array_keys(array_flip(array_diff($pathArray, $uriArray)));
        $this->args = array_combine($pathDiff, $uriDiff);
    }

    /**
     * @return bool
     */
    private function checkPath(): bool
    {
        $pathArray = explode('/', $this->path);
        $uriArray = explode('/', $this->uri);

        $intersect = array_intersect($pathArray, $uriArray);
        $filteredUri = [];
        foreach (array_diff($uriArray, $pathArray) as $index => $item) {
            $filteredUri = array_filter([...$uriArray], fn($i) => $i !== $item);
        }

        return implode('/', $intersect) === implode('/', $filteredUri);
    }


    public function __destruct()
    {
        $this->prefix = null;
        $this->pattern = null;
        $this->path = null;
        $this->args = [];
    }
}