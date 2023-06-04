<?php

namespace ZankoKhaledi\PhpSimpleRouter\Interfaces;

interface IRoute
{
    public function middleware(array $middlewares):IRoute;

    public function where(string $pattern):IRoute;
}