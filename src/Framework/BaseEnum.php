<?php


namespace Lib\Framework;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\ConstantsCollector;
use InvalidArgumentException;
use LogicException;
use ReflectionClass;


class BaseEnum extends AbstractConstants
{

    /**
     * The selected enumerator value
     *
     * @var null|bool|int|float|string
     */
    private $value;

    /**
     * The ordinal number of the enumerator
     *
     * @var null|int
     */
    private $ordinal;

    /**
     * A map of enumerator names and values by enumeration class
     *
     * @var array ["$class" => ["$name" => $value, ...], ...]
     */
    private static $constants = [];

    /**
     * A List of available enumerator names by enumeration class
     *
     * @var array ["$class" => ["$name0", ...], ...]
     */
    private static $names = [];

    /**
     * Already instantiated enumerators
     *
     * @var array ["$class" => ["$name" => $instance, ...], ...]
     */
    private static $instances = [];

    /**
     * Constructor
     *
     * @param null|bool|int|float|string $value   The value of the enumerator
     * @param int|null                   $ordinal The ordinal number of the enumerator
     */
    final private function __construct($value, $ordinal = null)
    {
        $this->value   = $value;
        $this->ordinal = $ordinal;
    }

    /**
     * Get the value of the enumerator
     *
     * @return null|bool|int|float|string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the name of the enumerator
     *
     * @return string
     */
    public function getName()
    {
        $ordinal = $this->ordinal !== null ? $this->ordinal : $this->getOrdinal();
        return self::$names[static::class][$ordinal];
    }

    /**
     * Get the name of the enumerator
     *
     * @return string
     */
    public function getMessage()
    {
        $class = get_called_class();
        return ConstantsCollector::getValue($class, $this->value, 'message');
    }

    /**
     * Get the ordinal number of the enumerator
     *
     * @return int
     */
    final public function getOrdinal()
    {
        if ($this->ordinal === null) {
            $ordinal = 0;
            $value   = $this->value;
            foreach (self::detectConstants(static::class) as $constValue) {
                if ($value === $constValue) {
                    break;
                }
                ++$ordinal;
            }

            $this->ordinal = $ordinal;
        }

        return $this->ordinal;
    }

    /**
     * Get an enumerator instance by the given value
     *
     * @param mixed $value
     * @return static
     * @throws InvalidArgumentException On an unknown or invalid value
     * @throws LogicException           On ambiguous constant values
     */
    public static function byValue($value)
    {
        if (!isset(self::$constants[static::class])) {
            self::detectConstants(static::class);
        }

        $name = \array_search($value, self::$constants[static::class], true);
        if ($name === false) {
            throw new InvalidArgumentException(sprintf(
                'Unknown value %s for enumeration %s',
                \is_scalar($value)
                    ? \var_export($value, true)
                    : 'of type ' . (\is_object($value) ? \get_class($value) : \gettype($value)),
                static::class
            ));
        }

        if (!isset(self::$instances[static::class][$name])) {
            self::$instances[static::class][$name] = new static(self::$constants[static::class][$name]);
        }

        return self::$instances[static::class][$name];
    }

    /**
     * Get an enumerator instance by the given name
     *
     * @param string $name The name of the enumerator
     * @return static
     * @throws InvalidArgumentException On an invalid or unknown name
     * @throws LogicException           On ambiguous values
     */
    public static function byName($name)
    {
        $name = (string) $name;
        if (isset(self::$instances[static::class][$name])) {
            return self::$instances[static::class][$name];
        }

        $const = static::class . '::' . $name;
        if (!\defined($const)) {
            throw new InvalidArgumentException($const . ' not defined');
        }

        return self::$instances[static::class][$name] = new static(\constant($const));
    }

    /**
     * Get a list of enumerator values ordered by ordinal number
     *
     * @return mixed[]
     */
    final public static function getValues()
    {
        return \array_values(self::detectConstants(static::class));
    }

    /**
     * Get a list of enumerator names ordered by ordinal number
     *
     * @return string[]
     */
    final public static function getNames()
    {
        if (!isset(self::$names[static::class])) {
            self::detectConstants(static::class);
        }
        return self::$names[static::class];
    }

    /**
     * Get all available constants of the called class
     *
     * @return array
     * @throws LogicException On ambiguous constant values
     */
    final public static function getConstants()
    {
        return self::detectConstants(static::class);
    }

    /**
     * Is the given enumerator part of this enumeration
     *
     * @param static|null|bool|int|float|string $value
     * @return bool
     */
    public static function has($value)
    {
        if ($value instanceof static && \get_class($value) === static::class) {
            return true;
        }

        $constants = self::detectConstants(static::class);
        return \in_array($value, $constants, true);
    }

    /**
     * Detect all public available constants of given enumeration class
     *
     * @param string $class
     * @return array
     */
    private static function detectConstants($class)
    {
        if (!isset(self::$constants[$class])) {
            $reflection = new ReflectionClass($class);
            $publicConstants  = [];

            do {
                $scopeConstants = [];
                if (\PHP_VERSION_ID >= 70100 && method_exists(ReflectionClass::class, 'getReflectionConstants')) {
                    // Since PHP-7.1 visibility modifiers are allowed for class constants
                    // for enumerations we are only interested in public once.
                    // NOTE: HHVM > 3.26.2 still does not support private/protected constants.
                    //       It allows the visibility keyword but ignores it.
                    foreach ($reflection->getReflectionConstants() as $reflectConstant) {
                        if ($reflectConstant->isPublic()) {
                            $scopeConstants[ $reflectConstant->getName() ] = $reflectConstant->getValue();
                        }
                    }
                } else {
                    // In PHP < 7.1 all class constants were public by definition
                    $scopeConstants = $reflection->getConstants();
                }

                $publicConstants = $scopeConstants + $publicConstants;
            } while (($reflection = $reflection->getParentClass()) && $reflection->name !== __CLASS__);

            assert(
                self::noAmbiguousValues($publicConstants),
                "Ambiguous enumerator values detected for {$class}"
            );

            self::$constants[$class] = $publicConstants;
            self::$names[$class] = \array_keys($publicConstants);
        }

        return self::$constants[$class];
    }

    /**
     * Test that the given constants does not contain ambiguous values
     * @param array $constants
     * @return bool
     */
    private static function noAmbiguousValues(array $constants)
    {
        foreach ($constants as $value) {
            $names = \array_keys($constants, $value, true);
            if (\count($names) > 1) {
                return false;
            }
        }

        return true;
    }
}