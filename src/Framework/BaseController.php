<?php

declare(strict_types=1);


namespace CC\Hyperf\Common\Framework;

use Psr\Http\Message\ResponseInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\Context;
use CC\Hyperf\Common\Framework\Http\Response;
use CC\Hyperf\Common\Constants\Error;
use CC\Hyperf\Common\Exception\BusinessException;
use CC\Hyperf\Common\Exception\RuntimeException;
use CC\Hyperf\Common\Component\Validator\Validator;
use Psr\Container\ContainerInterface;

class BaseController
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

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var array
     */
    protected $validateContext = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->response = $container->get(ResponseInterface::class);
        $this->request = $container->get(RequestInterface::class);

        $this->validator = new Validator();
    }

    public function attribute($key, $defaultValue = null)
    {
        return $this->request->getAttribute($key, $defaultValue);
    }

    /**
     * @param $key
     * @return array|mixed|string
     * @throws BusinessException
     */
    protected function required($key) {
        $value = $this->request->input($key, null);
        if ($value === null) {
            throw new BusinessException(Error::INVALID_PARAMS);
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
        return $this->request->input($key, $defaultValue);
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
            throw new BusinessException(Error::INVALID_PARAMS);
        }
        return $enumClass::byValue($value);
    }

    public function validate(callable $callback)
    {
        $name = $this->request->getPathInfo();

        if(!isset($this->validateContext[$name])) {
            $this->validator->context($name, function(Validator $context) use ($callback) {
                call_user_func($callback, $context);
            });
        }

        $result = $this->validator->validate($this->request->all(), $name);

        if($result->isNotValid()) {
            throw new BusinessException(Error::INVALID_PARAMS, json_encode($result->getMessages()));
        }

        $input = $result->getValues();

        Context::set('http.request.parsedData', $input);
    }
}
