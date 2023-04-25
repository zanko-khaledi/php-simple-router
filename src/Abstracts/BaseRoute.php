<?php
declare(strict_types=1);

namespace ZankoKhaledi\PhpSimpleRouter\Abstracts;

abstract class BaseRoute
{
    protected static ?array $routes = [];
    protected ?string $name = null;

    /**
     * @param string|null $name
     * @param string $path
     * @return void
     */
    public function setRoute(?string $name, string $path)
    {
        static::$routes[$name] = $path;
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
     * @return string|null
     */
    public static function route(string $name): ?string
    {
        return static::$routes[$name];
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