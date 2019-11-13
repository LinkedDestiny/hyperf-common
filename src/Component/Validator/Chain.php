<?php
declare(strict_types=1);


namespace Lib\Component\Validator;

use Lib\Component\Validator\Rules\IP;
use Lib\Component\Validator\Rules\EnumClass;
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

    /**
     * @return Chain
     */
    public function ip()
    {
        return $this->addRule(new IP());
    }
}