<?php
declare(strict_types=1);


namespace Lib\Middleware;

use Common\Helper\TokenGenerator;
use Firebase\JWT\ExpiredException;
use Hyperf\Utils\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class TokenMiddleware implements MiddlewareInterface
{

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $request->getHeaderLine('authorization');
        if (empty($token)) {
            $token = $request->getParsedBody()['authorization'] ?? null;
        }

        if(!empty($token)) {
            $tokenExpire = false;
            try {
                $message = TokenGenerator::verifyToken($token, config('jwt-key', ''));
            } catch (ExpiredException $e) {
                $tokenExpire = true;
                $message = [];
            } catch (\Throwable $e) {
                $message = [];
            }

            $request = $request->withAttribute('token_expire', $tokenExpire);
            $request = $request->withAttribute('user_id', $message['user_id'] ?? null);
            $request = $request->withAttribute('message', $message);

            Context::set(ServerRequestInterface::class, $request);

            //Context::set($request);
        }
        $response = $handler->handle($request);
        return $response;
    }
}