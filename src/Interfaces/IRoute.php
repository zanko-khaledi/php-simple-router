<?php

namespace ZankoKhaledi\PhpSimpleRouter\Interfaces;

interface IRoute
{
    public function addRoute(string $method,string $path,string $pattern = null,callable|array $callback);
}