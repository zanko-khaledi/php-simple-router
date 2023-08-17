<?php

namespace App\Controllers;

use App\Models\Foo;
use ZankoKhaledi\PhpSimpleRouter\HTTPResponse;
use ZankoKhaledi\PhpSimpleRouter\Request;

class FooController
{

    protected ?Foo $model = null;

    public function __construct()
    {
        $this->model = new Foo();
    }


    public function index(Request $request)
    {
        $data = $this->model->all();

        $httpResponse = new HTTPResponse();

        $response = $httpResponse->json($data,200);

        echo $response;
    }


    public function store(Request $request)
    {
        $model = $this->model;

        $response = $model->create([
            'name' => $request->post('name')
        ]);

        $httpResponse = new HTTPResponse();

        echo $httpResponse->json([
            'message' => $response
        ],HTTPResponse::CREATED);
    }
}