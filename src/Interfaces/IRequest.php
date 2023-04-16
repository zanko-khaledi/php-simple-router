<?php

namespace ZankoKhaledi\PhpSimpleRouter\Interfaces;

interface IRequest
{
    public function server();

    public function ajax(): bool;

    public function query(): object;

    public function params(): object;

    public function get(string $key);

    public function post(string $key);

    public function input(string $key);

    public function all();
}