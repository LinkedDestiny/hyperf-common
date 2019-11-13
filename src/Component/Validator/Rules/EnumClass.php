<?php
declare(strict_types=1);


namespace Lib\Component\Validator\Rules;

use Lib\Exception\RuntimeException;
use Lib\Framework\BaseEnum;
use Particle\Validator\Rule;

class EnumClass extends Rule
{
    const WRONG = 'EnumClass::WRONG';

    protected $messageTemplates = [
        self::WRONG => '{{ name }} with value {{ value }} is not value of "{{ class_name }}"',
    ];

    /**
     * @var string
     */
    protected $className;

    public function __construct(string $className)
    {
        $this->className = $className;
    }

    /**
     * This method should validate, possibly log errors, and return the result as a boolean.
     *
     * @param mixed $value
     * @return bool
     */
    public function validate($value)
    {
        if (!class_exists($this->className) || !is_subclass_of($this->className, BaseEnum::class)) {
            throw new RuntimeException($this->className . ' not exists');
        }

        $result = call_user_func("{$this->className}::has", $value);

        if (!$result) {
            return $this->error(self::WRONG);
        }

        return true;
    }

    protected function getMessageParameters()
    {
        return array_merge(parent::getMessageParameters(), [
            'class_name' => $this->className,
        ]);
    }
}