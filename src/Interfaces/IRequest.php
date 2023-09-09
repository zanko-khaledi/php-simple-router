<?php

namespace ZankoKhaledi\PhpSimpleRouter\Interfaces;

interface IRequest extends IUpload
{
    public function server();

    public function ajax(): bool;

    public function query(): object;

    public function params(): object;

    public function get(string $key);

    public function post(string $key);

    public function input(string $key);

    public function all();

    public function getHost();

    public function getProtocol();

    public function uri();

    public function ip();

    public function session(string $key = null);

    public function cookie(string $key = null);
}