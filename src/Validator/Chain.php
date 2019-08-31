<?php
declare(strict_types=1);


namespace Lib\Validator;

use Lib\Validator\Rules\EnumClass;
use Particle\Validator\Chain as DefaultChain;

class Chain extends DefaultChain
{
    /**
     * @param string $className
     * @return Chain
     */
    public function enumClass(string $className)
    {
        return $this->addRule(new EnumClass($className));
    }
}