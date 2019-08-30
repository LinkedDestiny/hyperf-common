<?php
declare(strict_types=1);


namespace App\Enum\Enums;


use Lib\Framework\BaseEnum;

class ValidateType extends BaseEnum
{
    CONST INSERT = 'insert';

    CONST UPDATE = 'update';
}