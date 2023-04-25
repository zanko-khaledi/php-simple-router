<?php
declare(strict_types=1);

namespace ZankoKhaledi\PhpSimpleRouter\Abstracts;

abstract class BaseRoute
{
    protected static ?array $routes = [];
    protected ?string $name = null;

    public function setRoute(?string $name, string $path)
    {
        static::$routes[$name] = $path;
    }

    public static function getRoutes(): ?array
    {
        return static::$routes;
    }

    public static function route(string $name): ?string
    {
        return static::$routes[$name];
    }

    public static function list()
    {
        foreach (static::getRoutes() as $name => $route){
            echo "$name --------------> $route <br>";
        }
    }
}