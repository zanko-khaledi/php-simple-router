<?php

namespace ZankoKhaledi\PhpSimpleRouter\Interfaces;

interface IRoute
{
    public function addRoute(string $method,string $path,callable|array $callback,string $pattern = null);
}