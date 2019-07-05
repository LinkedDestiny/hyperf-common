<?php
declare(strict_types=1);


namespace Lib\Framework;


use Hyperf\HttpServer\Contract\RequestInterface;
use Lib\Framework\Http\Response;
use Psr\Container\ContainerInterface;

class BaseService
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
     * @var RequestInterface
     */
    protected $request;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->response = $container->get(Response::class);
        $this->request = $container->get(RequestInterface::class);
    }
}