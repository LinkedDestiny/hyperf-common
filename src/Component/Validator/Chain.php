<?php
declare(strict_types=1);


namespace CC\Hyperf\Common\Component\Validator;

use CC\Hyperf\Common\Component\Validator\Rules\IP;
use CC\Hyperf\Common\Component\Validator\Rules\Each;
use CC\Hyperf\Common\Component\Validator\Rules\EnumClass;
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

    /**
     * Validates a value to be a nested array, which can then be validated using a new Validator instance.
     *
     * @param callable $callback
     * @return Chain
     */
    public function each(callable $callback)
    {
        return $this->addRule(new Each($callback));
    }

}