<?php
declare(strict_types=1);


namespace Lib\Validator;

use Particle\Validator\Validator as DefaultValidator;

/**
 * @method Chain required($key, $name = null, $allowEmpty = false)
 * @method Chain optional($key, $name = null, $allowEmpty = true)
 */
class Validator extends DefaultValidator
{
    /**
     * @param string $key
     * @param string $name
     * @param bool $required
     * @param bool $allowEmpty
     * @return Chain
     */
    protected function buildChain($key, $name, $required, $allowEmpty)
    {
        return new Chain($key, $name, $required, $allowEmpty);
    }
}