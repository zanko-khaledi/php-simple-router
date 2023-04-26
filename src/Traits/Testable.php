<?php
declare(strict_types=1);

namespace ZankoKhaledi\PhpSimpleRouter\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

trait Testable
{
    private array $params = [];
    private int $statusCode = 200;
    private mixed $responseData;
    private int $size;
    private array $headers;
    private ?Client $request = null;


    /**
     * @param string $baseUri
     * @return Client
     */
    public static function assertHttpRequest(string $baseUri): Client
    {
        return new Client([
            "base_uri" => $baseUri
        ]);
    }

    /**
     * @param string $baseUri
     * @return static
     */
    public static function assertRequest(string $baseUri): static
    {
        $instance = (new static());
        $instance->request = new Client([
            "base_uri" => $baseUri
        ]);
        return $instance;
    }

    /**
     * @param string $route
     * @param array $params
     * @return $this
     */
    public function assertGet(string $route, array $params): static
    {
        $this->sendAsyncRequest('GET', $route, $params);
        return $this;
    }

    /**
     * @param string $route
     * @param array $params
     * @return $this
     */
    public function assertPost(string $route, array $params): static
    {
        $this->sendAsyncRequest('POST', $route, $params);
        return $this;
    }

    /**
     * @param string $route
     * @param array $params
     * @return $this
     */
    public function assertPut(string $route, array $params): static
    {
        $this->sendAsyncRequest('PUT', $route, $params);
        return $this;
    }

    /**
     * @param string $route
     * @param array $params
     * @return $this
     */
    public function assertPatch(string $route, array $params): static
    {
        $this->sendAsyncRequest('PATCH', $route, $params);
        return $this;
    }

    /**
     * @param string $route
     * @param array $params
     * @return $this
     */
    public function assertDelete(string $route, array $params): static
    {
        $this->sendAsyncRequest('DELETE', $route, $params);
        return $this;
    }

    /**
     * @param string $method
     * @param string $route
     * @param array $params
     * @return void
     */
    private function sendAsyncRequest(string $method, string $route, array $params)
    {
        $promise = $this->request->{strtolower($method).'Async'}($route,$params)->then(function (Response $response){
            $this->responseData = $response->getBody()->getContents();
            $this->statusCode = $response->getStatusCode();
            $this->headers = $response->getHeaders();
            $this->size = $response->getBody()->getSize();
        });
        $promise->wait();
    }

    /**
     * @return int
     */
    public function status(): int
    {
        return $this->statusCode;
    }

    /**
     * @return string
     */
    public function body(): mixed
    {
        return $this->responseData;
    }

    /**
     * @return false|string
     */
    public function json(): bool|string
    {
        return is_string($this->responseData) ? $this->responseData : json_encode($this->responseData);
    }

    /**
     * @return int
     */
    public function size(): int
    {
        return $this->size;
    }

    /**
     * @return array
     */
    public function headers(string $key = null): array
    {
        return is_null($key) ? $this->headers : $this->headers[$key];
    }
}