<?php

namespace ZankoKhaledi\PhpSimpleRouter\Interfaces;

interface IResponse
{
    public function toJson();

    public function getResponseData();
}