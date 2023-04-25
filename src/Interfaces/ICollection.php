<?php

namespace ZankoKhaledi\PhpSimpleRouter\Interfaces;

interface ICollection
{
    public function loadRoutesFrom(string $path);

    public function loadRouteFrom(string $path);
}