<?php


namespace Lib\Middleware;

use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Class OptionsMiddleware
 * @package Lib\Middleware
 */
class OptionsMiddleware implements MiddlewareInterface
{

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (strtoupper($request->getMethod()) == 'OPTIONS') {
            $response = Context::get(ResponseInterface::class);
            return $response
                ->withAddedHeader('Access-Control-Expose-Headers', '*')
                ->withAddedHeader('Access-Control-Allow-Origin', '*')
                ->withAddedHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                ->withAddedHeader('Access-Control-Allow-Headers', '*');
        }

        $response = $handler->handle($request);
        return $response
            ->withAddedHeader('Access-Control-Expose-Headers', '*')
            ->withAddedHeader('Access-Control-Allow-Origin', '*')
            ->withAddedHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
            ->withAddedHeader('Access-Control-Allow-Headers', '*');
    }
}