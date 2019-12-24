<?php

declare(strict_types=1);


namespace CC\Hyperf\Common\Exception;

use Hyperf\Server\Exception\ServerException;
use CC\Hyperf\Common\Constants\Error;
use Throwable;

class BusinessException extends ServerException
{
    public function __construct(int $code = 0, string $message = null, Throwable $previous = null)
    {
        if (is_null($message)) {
            $error = di(Error::class);
            $message = $error->getMessage($code);
        }
        parent::__construct($message, $code, $previous);
    }
}
