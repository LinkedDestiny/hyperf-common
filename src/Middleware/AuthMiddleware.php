<?php
declare(strict_types=1);


namespace CC\Hyperf\Common\Middleware;

use CC\Hyperf\Common\Constants\Error;
use CC\Hyperf\Common\Exception\BusinessException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthMiddleware implements MiddlewareInterface
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
        $userId = $request->getAttribute('user_id');
        if (empty($userId)) {
            $tokenExpire = $request->getAttribute('token_expire');
            if ($tokenExpire) {
                throw new BusinessException(Error::TOKEN_EXPIRE);
            } else {
                throw new BusinessException(Error::INVALID_TOKEN);
            }
        }
        return $handler->handle($request);
    }
}