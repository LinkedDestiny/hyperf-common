<?php


namespace Lib\Framework;

use Hyperf\Constants\ConstantsCollector;
use MabeEnum\Enum;
use MabeEnum\EnumSerializableTrait;
use Serializable;

class BaseEnum extends Enum implements Serializable
{
    use EnumSerializableTrait;

    /**
     * Get the name of the enumerator
     *
     * @return string
     */
    public function getMessage()
    {
        $class = get_called_class();
        return ConstantsCollector::getValue($class, $this->getValue(), 'message');
    }
}