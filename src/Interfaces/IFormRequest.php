<?php

namespace ZankoKhaledi\PhpSimpleRouter\Interfaces;

use ZankoKhaledi\PhpSimpleRouter\Request;

interface IFormRequest
{
    public function validate(Request $request);
}