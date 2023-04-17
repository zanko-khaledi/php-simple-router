<?php

namespace ZankoKhaledi\PhpSimpleRouter;

use ZankoKhaledi\PhpSimpleRouter\Interfaces\IRequest;

class Request implements IRequest
{
    private ?object $server = null;
    private array $args = [];


    /**
     * @param array $args
     */
    public function __construct(array $args = [])
    {
        $this->server = new \stdClass();
        foreach ($_SERVER as $key => $value) {
            $this->server->{strtolower($key)} = $value;
        }
        $this->args = $args;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public function __get(string $name)
    {
        if (!in_array($name, (array)$this->server)) {
            throw new \Exception("property $name doesn't exists on collection instance request.");
        }
        return $this->server->{$name};
    }

    /**
     * @return object
     */
    public function server(): object
    {
        return $this->server;
    }

    /**
     * @return bool
     */
    public function ajax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
     * @return object
     */
    public function query(): object
    {
        return (object)$_GET ?? (object)$this->server->query_string;
    }

    /**
     * @return object
     */
    public function params(): object
    {
        return (object)$this->args;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        return array_key_exists($key, $_GET) ? $_GET[$key] : null;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function post(string $key)
    {
        return array_key_exists($key, $_POST) ? $_POST[$key] : null;
    }

    /**
     * @param string $key
     * @return bool|string
     */
    public function input(string $key): bool|string
    {
        return json_decode(file_get_contents("php://input"), false)->{$key} ?? '';
    }

    /**
     * @return mixed|object
     */
    public function all()
    {
        return match ($this->server()->request_method){
            'GET' => $this->query(),
            'POST' => (object)$_POST,
            'PUT'  => json_decode(file_get_contents("php://input"),false),
            'PATCH' => json_decode(file_get_contents("php://input"),false),
            'DELETE' => json_decode(file_get_contents("php://input"),false),
            default => $this->query()
        };
    }

    /**
     * @return array|false|int|string|null
     */
    public function getHost()
    {
        return $this->server()->http_host ?? parse_url($this->server()->request_uri,PHP_URL_HOST);
    }

    /**
     * @return array|false|int|string|null
     */
    public function uri()
    {
        return parse_url($this->server()->request_uri,PHP_URL_PATH);
    }

    /**
     * @return string
     */
    public function ip()
    {
        return gethostbyname($this->getHost());
    }

    /**
     * @return array|null
     */
    public function __debugInfo(): ?array
    {
        return (array)$this->server;
    }
}