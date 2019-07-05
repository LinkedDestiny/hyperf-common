<?php

declare(strict_types=1);


namespace Lib\Framework;

use Hyperf\HttpServer\Contract\RequestInterface;
use Lib\Framework\Http\Response;
use Lib\Constants\ErrorCode;
use Lib\Exception\BusinessException;
use Lib\Exception\RuntimeException;
use Psr\Container\ContainerInterface;

abstract class BaseController
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

    public function attribute($key, $defaultValue = null)
    {
        $value = $this->request->getAttribute($key, $defaultValue);
        return $value;
    }

    /**
     * @param $key
     * @return array|mixed|string
     * @throws BusinessException
     */
    protected function required($key) {
        $value = $this->request->input($key, null);
        if ($value === null) {
            throw new BusinessException(ErrorCode::INVALID_PARAMS);
        }
        return $value;
    }

    /**
     * @param $key
     * @param null $defaultValue
     * @return array|mixed|string
     * @throws BusinessException
     */
    protected function optional($key, $defaultValue = null) {
        $value = $this->request->input($key, $defaultValue);
        return $value;
    }

    /**
     * @param string $enumClass
     * @param $key
     * @param null $defaultValue
     * @return mixed
     * @throws BusinessException
     */
    protected function optionalEnum(string $enumClass, $key, $defaultValue = null) {
        if (!class_exists($enumClass) || !is_subclass_of($enumClass, BaseEnum::class)) {
            throw new RuntimeException($enumClass . ' not exists');
        }
        $value = $this->request->input($key, $defaultValue);
        if ($value === null) {
            return $value;
        }
        return $enumClass::byValue($value);
    }

    /**
     * @param string $enumClass
     * @param $key
     * @return mixed
     * @throws BusinessException
     */
    protected function requiredEnum(string $enumClass, $key) {
        if (!class_exists($enumClass) || !is_subclass_of($enumClass, BaseEnum::class)) {
            throw new RuntimeException($enumClass . ' not exists');
        }
        $value = $this->request->input($key, null);
        if ($value === null) {
            throw new BusinessException(ErrorCode::INVALID_PARAMS);
        }
        return $enumClass::byValue($value);
    }
}
