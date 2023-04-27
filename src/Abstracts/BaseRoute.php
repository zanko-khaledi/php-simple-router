<?php
declare(strict_types=1);

namespace ZankoKhaledi\PhpSimpleRouter\Abstracts;

abstract class BaseRoute
{
    protected static ?array $routes = [];
    protected ?string $name = null;

    /**
     * @param string|null $name
     * @param string $method
     * @param string $path
     * @param string|null $pattern
     * @param callable|array $callback
     * @return void
     */
    public function setRoute(?string $name, string $method, string $path, ?string $pattern, callable|array $callback)
    {
        if (!is_null($name)) {
            static::$routes[$name] = [
                "method" => $method,
                "path" => $path,
                "pattern" => $pattern,
                "callback" => $callback
            ];
        } else {
            static::$routes[] = [
                "method" => $method,
                "path" => $path,
                "pattern" => $pattern,
                "callback" => $callback
            ];
        }
    }

    /**
     * @return array|null
     */
    public static function getRoutes(): ?array
    {
        return static::$routes;
    }

    /**
     * @param string $name
     * @return array|null
     */
    public static function route(string $name): ?object
    {
        return (object)static::$routes[$name];
    }

    /**
     * @return void
     */
    public static function list()
    {
        foreach (static::getRoutes() as $name => $route) {
            echo "$name --------------> $route <br>";
        }
    }
}