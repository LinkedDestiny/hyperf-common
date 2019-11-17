<?php
declare(strict_types=1);

namespace Lib\Framework;

use Common\Helper\ArrayHelper;
use Lib\Exception\RuntimeException;

class BaseConfig
{
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

    public function toArray()
    {
        $values = get_object_vars($this);

        $result = [];
        foreach ($values as $name => $value) {
            if(is_object($value)) {
                if ($value instanceof BaseConfig) {
                    $result[$name] = $value->toArray();
                } else {
                    $result[$name] = get_object_vars($value);
                }
            } else if (is_array($value)) {
                foreach ($value as $key => $val) {
                    if ($val instanceof BaseConfig) {
                        $value[$key] = $val->toArray();
                    } else if (is_object($val)){
                        $value[$key] = get_object_vars($val);
                    } else {
                        $value[$key] = $val;
                    }
                }
                $result[$name] = $value;
            } else {
                $result[$name] = $value;
            }
        }
        return $result;
    }

    public function fromArray(array $values)
    {
        foreach ($values as $name => $value) {
            if(is_array($value)) {
                $value = $this->make($value);

                foreach ($value as $key => &$val) {
                    $val = $this->make($val);
                }
                unset($val);
            }

            $func = 'set' . ucfirst($name);
            if(method_exists($this, $func)) {
                $this->$func($value);
            } else {
                $this->$name = $value;
            }
        }

        return $this;
    }

    protected function make($val)
    {
        if(is_array($val) && isset($val['type']) && isset($val['value']) && class_exists($val['type'])) {
            $obj = new $val['type']();
            if ($obj instanceof BaseConfig) {
                $obj->fromArray($val['value']);
                $val = $obj;
            }
        }
        return $val;
    }
}