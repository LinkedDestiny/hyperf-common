<?php
declare(strict_types=1);

namespace Lib\Contracts\Traits;

use ReflectionProperty;
use Lib\Component\PhpDocReader;

trait ArrayTrait
{
    public function toArray()
    {
        $values = get_object_vars($this);

        $result = [];
        foreach ($values as $name => $value) {
            if(is_object($value)) {
                if (method_exists($value, 'toArray')) {
                    $result[$name] = $value->toArray();
                } else {
                    $result[$name] = get_object_vars($value);
                }
            } else if (is_array($value)) {
                foreach ($value as $key => $val) {
                    if (method_exists($val, 'toArray')) {
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
        $reader = new PhpDocReader();

        foreach ($values as $name => $value) {
            if (is_array($value)) {
                try {
                    $property = new ReflectionProperty(static::class, $name);
                    $propertyClass = $reader->getPropertyClass($property);
                    if(is_array($propertyClass)) {
                        $propertyClass = $propertyClass[0];
                        foreach ($value as $key => &$val) {
                            $val = $this->ArrayTrait_make($propertyClass, $val);
                        }
                        unset($val);
                    } else if(is_string($propertyClass)) {
                        $value = $this->ArrayTrait_make($propertyClass, $value);
                    }
                } catch (\Exception $e) {
                }
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

    private function ArrayTrait_make($class, $val)
    {
        $obj = new $class();
        if (method_exists($obj, 'fromArray')) {
            $obj->fromArray($val);
            $val = $obj;
        }
        return $val;
    }
}