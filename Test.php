<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ZankoKhaledi\PhpSimpleRouter\Router;

require_once __DIR__ . '/App/Controllers/FooController.php';

class Test extends TestCase
{

    protected string $baseUri = 'http://localhost:8000';

    public function test_get_route()
    {
        $request = Router::assertRequest($this->baseUri);
        $response = $request->assertGet('/foo', []);

        $this->assertEquals(200, $response->status());
        $this->assertJson($response->json());
        $this->assertSame(json_encode([
            'name' => 'Zanko'
        ]), $response->body());
    }


    public function test_zanko_post()
    {
        $request = Router::assertRequest($this->baseUri);
        $response = $request->assertPost('/foo', [
            'form_params' => [
                'name' => 'Teddy'
            ]
        ]);

        $this->assertEquals(201, $response->status());
        $this->assertJson($response->json());
        $this->assertSame(json_encode([
            'name' => 'Foo'
        ]), $response->json());
    }

}