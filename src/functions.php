<?php
declare(strict_types=1);

use Hyperf\ExceptionHandler\Formatter\FormatterInterface;
use Hyperf\HttpMessage\Server\Request;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Context;
use Lib\Framework\Http\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

if (! function_exists('isDev')) {
    /**
     * @return bool
     */
    function isDev()
    {
        return in_array(config('env', 'dev'), ['dev', 'test']);
    }
}

if (! function_exists('request')) {
    /**
     * @return Request
     */
    function request()
    {
        return Context::get(ServerRequestInterface::class);
    }

}


if (! function_exists('response')) {
    /**
     * @return Response
     */
    function response()
    {
        return Context::get(PsrResponseInterface::class);
    }

}

if (! function_exists('di')) {
    /**
     * Finds an entry of the container by its identifier and returns it.
     * @param null|mixed $id
     * @return mixed|ContainerInterface
     */
    function di($id = null)
    {
        $container = ApplicationContext::getContainer();
        if ($id) {
            return $container->get($id);
        }

        return $container;
    }
}

if (! function_exists('format_throwable')) {
    /**
     * Format a throwable to string.
     * @param Throwable $throwable
     * @return string
     */
    function format_throwable(Throwable $throwable): string
    {
        return di()->get(FormatterInterface::class)->format($throwable);
    }
}
