<?php


namespace CC\Hyperf\Common\Middleware;

use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Class OptionsMiddleware
 * @package CC\Hyperf\Common\Middleware
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
        $header = $request->getHeaderLine('Access-Control-Request-Headers');
        if (empty($header)) {
            $header = '*';
        }

        // 设置跨域
        $response = Context::get(ResponseInterface::class);
        $response = $response->withAddedHeader('Access-Control-Expose-Headers', '*')
            ->withAddedHeader('Access-Control-Allow-Origin', '*')
            ->withAddedHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
            ->withAddedHeader('Access-Control-Allow-Headers', $header);
        Context::set(ResponseInterface::class, $response);

        if (strtoupper($request->getMethod()) == 'OPTIONS') {
            return $response;
        }

        $response = $handler->handle($request);
        return $response;
    }
}