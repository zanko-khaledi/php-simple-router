<?php

namespace ZankoKhaledi\PhpSimpleRouter\Interfaces;

interface IMiddleware
{
    public function handle(IRequest $request,callable $next);
}