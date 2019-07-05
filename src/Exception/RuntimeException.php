<?php

declare(strict_types=1);


namespace Lib\Exception;

use Hyperf\Server\Exception\ServerException;

class RuntimeException extends ServerException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
