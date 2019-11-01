<?php
declare(strict_types=1);


namespace Lib\Validator\Rules;

use Particle\Validator\Rule;

class IP extends Rule
{
    const WRONG = 'IP::WRONG';

    protected $messageTemplates = [
        self::WRONG => '{{ name }} with value {{ value }} is not a valid IP address"',
    ];

    public function __construct()
    {

    }

    /**
     * This method should validate, possibly log errors, and return the result as a boolean.
     *
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        if (!filter_var($value, FILTER_VALIDATE_IP)) {
            return $this->error(self::WRONG);
        }
        return true;
    }
}