<?php
declare(strict_types=1);

namespace ZankoKhaledi\PhpSimpleRouter;

use ZankoKhaledi\PhpSimpleRouter\Interfaces\ICollection;


class RouterCollection implements ICollection
{
    public function loadRoutesFrom(string $path)
    {
        $routes = glob($path);
        if (is_array($routes)) {
            foreach ($routes as $route) {
                if (file_exists($route)) {
                    require $route;
                } else {
                    throw new \Exception("$route file doesn't exists.");
                }
            }
        }
    }

    public function loadRouteFrom(string $path)
    {
        if (file_exists($path)) {
            require $path;
        } else {
            throw new \Exception("$path file doesn't exists.");
        }
    }
}