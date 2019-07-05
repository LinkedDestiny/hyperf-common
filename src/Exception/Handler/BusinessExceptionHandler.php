<?php

declare(strict_types=1);


namespace Lib\Exception\Handler;

use Hyperf\Config\Annotation\Value;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Lib\Framework\Http\Response;
use Lib\Constants\ErrorCode;
use Lib\Exception\BusinessException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class BusinessExceptionHandler extends ExceptionHandler
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    /**
     * @Value("env")
     */
    protected $env;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->response = $container->get(Response::class);
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        if ($throwable instanceof BusinessException) {
            $this->logger->warning(format_throwable($throwable));
            if (isDev()) {
                var_dump(format_throwable($throwable));
            }
            return $this->response->fail($throwable->getCode(), $throwable->getMessage());
        }

        $this->logger->error(format_throwable($throwable));
        $message = 'Server Error！';
        if (isDev()) {
            $message = format_throwable($throwable);
        }
        return $this->response->fail(ErrorCode::SERVER_ERROR, $message);
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
