<?php
declare(strict_types=1);


namespace Lib\Framework\Http;


use Hyperf\HttpServer\Exception\Http\EncodingException;
use Hyperf\Utils\Contracts\Arrayable;
use Hyperf\Utils\Contracts\Jsonable;

class HttpResponse extends \Hyperf\HttpServer\Response
{
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