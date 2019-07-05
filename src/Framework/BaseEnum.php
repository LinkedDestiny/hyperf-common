<?php


namespace Lib\Framework;

use MabeEnum\Enum;
use MabeEnum\EnumSerializableTrait;
use Serializable;

class BaseEnum extends Enum implements Serializable
{
    use EnumSerializableTrait;
}