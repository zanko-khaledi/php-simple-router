<?php
declare(strict_types=1);

namespace ZankoKhaledi\PhpSimpleRouter;

use ZankoKhaledi\PhpSimpleRouter\Interfaces\IResponse;

class HTTPResponse implements IResponse
{
    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    const NON_AUTHORITATIVE_INFORMATION = 203;
    const NO_CONTENT = 204;
    const RESET_CONTENT = 205;
    const PARTIAL_CONTENT = 206;


    const MULTIPLE_CHOICES = 300;
    const MOVED_PERMANENTLY = 301;
    const FOUND = 302;
    const SEE_OTHER = 303;
    const NOT_MODIFIED = 304;
    const TEMPORARY_REDIRECT = 307;
    const PERMANENT_REDIRECT = 308;

    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const PAYMENT_REQUIRED = 402;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const NOT_ACCEPTED = 406;
    const UNPROCESSABLE_CONTENT = 422;

    const INTERNAL_SERVER_ERROR = 500;
    const NOT_IMPLEMENTED = 501;
    const BAD_GATEWAY = 502;
    const SERVICE_UNAVAILABLE = 503;


    private array $data = [];

    /**
     * @param array $data
     * @param int $status
     * @return string
     */
    public function json(array $data, int $status = self::OK): string
    {
        header("Content-type:application/json");
        http_response_code($status);
        return json_encode($data);
    }

    /**
     * @param string $data
     * @param int $status
     * @return string
     */
    public function plainText(string $data, int $status = self::OK): string
    {
        header("Content-Type:text/plain;charset=UTF-8");
        http_response_code($status);
        return $data;
    }

    /**
     * @param string $data
     * @param int $status
     * @return string
     */
    public function plainHtml(string $data, int $status = self::OK): string
    {
        header("Content-Type:text/html;charset=UTF-8");
        http_response_code($status);
        return $data;
    }

    /**
     * @param string $url
     * @param int $status
     * @return void
     */
    public function redirect(string $url, int $status = self::MOVED_PERMANENTLY): void
    {
        header("Location:{$url}");
        http_response_code($status);
        exit();
    }

    /**
     * @param array $data
     * @param int $status
     * @return IResponse
     */
    public function withErrors(array $data, int $status = self::UNPROCESSABLE_CONTENT): IResponse
    {
        http_response_code($status);
        $this->data = [...$data];

        return $this;
    }

    /**
     * @return string
     *
     */
    public function toJson(): string
    {
        header("Content-type:application/json");
        return json_encode($this->data);
    }

    /**
     * @return array
     */
    public function getResponseData(): array
    {
        return $this->data;
    }
}