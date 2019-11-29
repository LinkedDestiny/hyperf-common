<?php
declare(strict_types=1);

namespace Lib\Framework;

use Common\Helper\ArrayHelper;
use Lib\Exception\RuntimeException;
use Xes\SDK\Common\Contracts\Traits\ArrayTrait;

class BaseConfig
{
    use ArrayTrait;

    /**
     * @param string $key 配置的键值
     * @param mixed|null $default 默认值
     * @param bool $throw 是否强制抛出异常
     * @return array|mixed
     * @throws RuntimeException
     */
    public function get(string $key, $default = null, bool $throw = false)
    {
        $value = ArrayHelper::get($this, $key, $default);
        if($throw && $value === null) {
            throw new RuntimeException($key);
        }
        return $value;
    }

    /**
     * @param string $key 配置的键值
     * @return array|mixed
     */
    public function has(string $key)
    {
        return ArrayHelper::has($this, $key);
    }

    /**
     * @param string $key       配置的键值
     * @param mixed $value      设置的值
     * @return mixed
     */
    public function set(string $key, $value)
    {
        ArrayHelper::set($this, $key, $value);
        return $value;
    }
}