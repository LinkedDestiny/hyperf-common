<?php

namespace Lib\Component;

use Reflector;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use ReflectionParameter;
use Common\Helper\StringHelper;
use PhpDocReader\AnnotationException;
use PhpDocReader\PhpParser\UseStatementParser;

/**
 * PhpDoc reader
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class PhpDocReader
{
    /**
     * @var UseStatementParser
     */
    private $parser;

    private $ignoredTypes = [
        'bool',
        'boolean',
        'string',
        'int',
        'integer',
        'float',
        'double',
        'array',
        'object',
        'callable',
        'resource',
        'mixed',
        'iterable',
    ];

    /**
     * Enable or disable throwing errors when PhpDoc Errors occur (when parsing annotations)
     *
     * @var bool
     */
    private $ignorePhpDocErrors;

    /**
     *
     * @param bool $ignorePhpDocErrors
     */
    public function __construct($ignorePhpDocErrors = false)
    {
        $this->parser = new UseStatementParser();
        $this->ignorePhpDocErrors = $ignorePhpDocErrors;
    }

    /**
     * Parse the docblock of the property to get the class of the var annotation.
     *
     * @param ReflectionProperty $property
     *
     * @return array|string|null Type of the property (content of var annotation)
     * @throws AnnotationException
     */
    public function getPropertyClass(ReflectionProperty $property)
    {
        $isArray = false;
        // Get the content of the @var annotation
        if (preg_match('/@var\s+([^\s]+)/', $property->getDocComment(), $matches)) {
            list(, $type) = $matches;
        } else {
            return null;
        }

        // Ignore types containing special characters ([], <> ...)
        if (!preg_match('/^[a-zA-Z0-9\\\\_\[\]]+$/', $type)) {
            return null;
        }

        $class = $property->getDeclaringClass();

        // If it is a array of class
        if (StringHelper::endWith($type, '[]')) {
            $type = StringHelper::subEnd($type, '[]');
            $isArray = true;
        }

        // Ignore primitive types
        if (in_array($type, $this->ignoredTypes)) {
            return null;
        }

        // If the class name is not fully qualified (i.e. doesn't start with a \)
        if ($type[0] !== '\\') {
            // Try to resolve the FQN using the class context
            $resolvedType = $this->tryResolveFqn($type, $class, $property);

            if (!$resolvedType && !$this->ignorePhpDocErrors) {
                throw new AnnotationException(sprintf(
                    'The @var annotation on %s::%s contains a non existent class "%s". '
                    . 'Did you maybe forget to add a "use" statement for this annotation?',
                    $class->name,
                    $property->getName(),
                    $type
                ));
            }

            $type = $resolvedType;
        }

        if (!$this->classExists($type) && !$this->ignorePhpDocErrors) {
            throw new AnnotationException(sprintf(
                'The @var annotation on %s::%s contains a non existent class "%s"',
                $class->name,
                $property->getName(),
                $type
            ));
        }

        // Remove the leading \ (FQN shouldn't contain it)
        $type = ltrim($type, '\\');

        if ($isArray) {
            return [$type];
        }
        return $type;
    }

    /**
     * Attempts to resolve the FQN of the provided $type based on the $class and $member context.
     *
     * @param string $type
     * @param ReflectionClass $class
     * @param Reflector $member
     *
     * @return string|null Fully qualified name of the type, or null if it could not be resolved
     */
    private function tryResolveFqn($type, ReflectionClass $class, Reflector $member)
    {
        $alias = ($pos = strpos($type, '\\')) === false ? $type : substr($type, 0, $pos);
        $loweredAlias = strtolower($alias);

        // Retrieve "use" statements
        $uses = $this->parser->parseUseStatements($class);

        if (isset($uses[$loweredAlias])) {
            // Imported classes
            if ($pos !== false) {
                return $uses[$loweredAlias] . substr($type, $pos);
            } else {
                return $uses[$loweredAlias];
            }
        } elseif ($this->classExists($class->getNamespaceName() . '\\' . $type)) {
            return $class->getNamespaceName() . '\\' . $type;
        } elseif (isset($uses['__NAMESPACE__']) && $this->classExists($uses['__NAMESPACE__'] . '\\' . $type)) {
            // Class namespace
            return $uses['__NAMESPACE__'] . '\\' . $type;
        } elseif ($this->classExists($type)) {
            // No namespace
            return $type;
        }

        if (version_compare(phpversion(), '5.4.0', '<')) {
            return null;
        } else {
            // If all fail, try resolving through related traits
            return $this->tryResolveFqnInTraits($type, $class, $member);
        }
    }

    /**
     * Attempts to resolve the FQN of the provided $type based on the $class and $member context, specifically searching
     * through the traits that are used by the provided $class.
     *
     * @param string $type
     * @param ReflectionClass $class
     * @param Reflector $member
     *
     * @return string|null Fully qualified name of the type, or null if it could not be resolved
     */
    private function tryResolveFqnInTraits($type, ReflectionClass $class, Reflector $member)
    {
        /** @var ReflectionClass[] $traits */
        $traits = [];

        // Get traits for the class and its parents
        while ($class) {
            $traits = array_merge($traits, $class->getTraits());
            $class = $class->getParentClass();
        }

        foreach ($traits as $trait) {
            // Eliminate traits that don't have the property/method/parameter
            if ($member instanceof ReflectionProperty && !$trait->hasProperty($member->name)) {
                continue;
            } elseif ($member instanceof ReflectionMethod && !$trait->hasMethod($member->name)) {
                continue;
            } elseif ($member instanceof ReflectionParameter && !$trait->hasMethod($member->getDeclaringFunction()->name)) {
                continue;
            }

            // Run the resolver again with the ReflectionClass instance for the trait
            $resolvedType = $this->tryResolveFqn($type, $trait, $member);

            if ($resolvedType) {
                return $resolvedType;
            }
        }
        return null;
    }

    /**
     * @param string $class
     * @return bool
     */
    private function classExists($class)
    {
        return class_exists($class) || interface_exists($class);
    }
}
