<?php
declare(strict_types=1);


namespace Lib\Framework\Http;


use Hyperf\Utils\Context;
use Hyperf\HttpMessage\Cookie\Cookie;
use Hyperf\HttpServer\Exception\Http\EncodingException;
use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\Contracts\Jsonable;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class Response extends \Hyperf\HttpServer\Response
{
    public function success($data = [])
    {
        return $this->json([
            'code' => 0,
            'msg'  => '',
            'data' => empty($data) ? new \stdClass() : $data,
        ]);
    }

    public function fail($code, $message = '')
    {
        return $this->json([
            'code' => $code,
            'msg' => $message,
            'data'  => new \stdClass()
        ]);
    }

    public function cookie(Cookie $cookie)
    {
        $response = $this->withCookie($cookie);
        Context::set(PsrResponseInterface::class, $response);
        return $this;
    }

    public function header(string $name, string $value)
    {
        $response = $this->withAddedHeader($name, $value);
        Context::set(PsrResponseInterface::class, $response);
        return $this;
    }

    protected function toJson($data): string
    {
        if (is_array($data)) {
            return json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        }

        if ($data instanceof Jsonable) {
            return (string) $data;
        }

        if ($data instanceof Arrayable) {
            return json_encode($data->toArray(), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        }

        throw new EncodingException('Error encoding response data to JSON.');
    }
}