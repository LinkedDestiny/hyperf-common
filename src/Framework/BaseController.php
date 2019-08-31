<?php

declare(strict_types=1);


namespace Lib\Framework;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\Context;
use Lib\Enum\ValidateType;
use Lib\Framework\Http\Response;
use Lib\Constants\ErrorCode;
use Lib\Exception\BusinessException;
use Lib\Exception\RuntimeException;
use Lib\Validator\Validator;
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

    /**
     * @var Validator
     */
    protected $validator;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->response = $container->get(Response::class);
        $this->request = $container->get(RequestInterface::class);

        $this->validator = new Validator();

        $this->validator->context(ValidateType::INSERT, [$this, 'validateInsert']);
        $this->validator->context(ValidateType::UPDATE, [$this, 'validateUpdate']);
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

    protected function validate(string $type)
    {
        $input = $this->request->all();

        $result = $this->validator->validate($input, $type);

        if($result->isNotValid()) {
            throw new BusinessException(ErrorCode::INVALID_PARAMS, json_encode($result->getMessages()));
        }

        $input = $result->getValues();

        Context::set('http.request.parsedData', $input);
    }

    public function validateInsert(Validator $validator)
    {

    }

    public function validateUpdate(Validator $validator)
    {

    }

    public function initValidator()
    {

    }

}
