<?php

namespace ZankoKhaledi\PhpSimpleRouter\Interfaces;

interface IRoute
{
    public function addRoute(string $method,string $path,callable|array $callback):IRoute;

    public function where(string $pattern):IRoute;

    public function middleware(array $middlewares):IRoute;

    public function serve();
}