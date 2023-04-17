<?php
declare(strict_types=1);

namespace ZankoKhaledi\PhpSimpleRouter;


use ZankoKhaledi\PhpSimpleRouter\Interfaces\IRoute;

final class Router implements IRoute
{
    private ?string $prefix = null;
    private ?string $pattern = null;
    private array $args = [];
    private ?string $path = null;
    private ?string $uri = null;
    private array $validMethods = [
        'GET', 'POST', 'PUT', 'PATCH', 'DELETE'
    ];

    /**
     *
     */
    public function __construct()
    {
        $this->uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
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
     * @param string $prefix
     * @param callable $callback
     * @return void
     */
    public function group(string $prefix,callable $callback)
    {
        $this->prefix = $prefix;
        $callback($this);
    }


    /**
     * @param string $method
     * @param mixed $path
     * @param callable|array $callback
     * @param string|null $pattern
     * @return void
     */
    public function addRoute(string $method, mixed $path,callable|array $callback,string $pattern = null)
    {
        if (!in_array($method, $this->validMethods)) {
            throw new \BadMethodCallException("$method not allowed");
        }
        $this->path = $this->prefix . $path;
        $this->where($pattern);
        $this->checkRequestMethod($method, $this->path, $callback);
    }


    /**
     * @param string $method
     * @param string $path
     * @param callable|array $callback
     * @return void
     */
    private function checkRequestMethod(string $method, string $path, callable|array $callback)
    {
        if ($method === $_SERVER['REQUEST_METHOD']) {
            match ($method) {
                'GET' => $this->handleRoute($path, $callback),
                'POST' => $this->handleRoute($path, $callback),
                'PUT' => $this->handleRoute($path, $callback),
                'PATCH' => $this->handleRoute($path, $callback),
                'DELETE' => $this->handleRoute($path, $callback),
                default => $this->handleRoute($path, $callback)
            };
        }
    }

    /**
     * @param string $path
     * @param array|callable $callback
     * @return void
     */
    private function handleRoute(string $path, array|callable $callback)
    {
        if ($this->pattern !== null) {
            if ($this->uri === $this->pattern) {
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
        if (is_callable($callback)) {
            $callback(new Request($this->args));
        }
        if (is_array($callback)) {
            call_user_func_array([new $callback[0], $callback[1]], [new Request($this->args)]);
        }
    }

    /**
     * @param string|null $pattern
     * @return void
     */
    private function where(string $pattern = null)
    {
        if ($pattern !== null) {
            preg_match($pattern, $this->uri, $matches);
            if (count($matches) > 0) {
                $this->pattern = '/' . $matches[0];
            } else {
                $this->pattern = null;
            }
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
     *
     */
    public function __destruct()
    {
        $this->prefix = null;
        $this->pattern = null;
        $this->path = null;
        $this->args = [];
    }

    private function groupCallback(callable $callback):IRoute
    {
        return $callback();
    }
}