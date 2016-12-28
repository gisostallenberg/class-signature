<?php
namespace GisoStallenberg\ClassSignature;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class ClassSignature
{
    /**
     * The class to generate a signature for
     *
     * @var string
     */
    private $class;

    /**
     * Export only public properties and methods
     *
     * @var boolean
     */
    private $onlyPublic = false;

    /**
     * Anonymise the parameters
     *
     * @var boolean
     */
    private $anonymiseParameters = false;

    /**
     * Conversion to be done on methods and their parameters
     *
     * @var mixed
     */
    private $caseConversion = false;

    /**
     * @param string $class
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    /**
     *
     * @param string $class
     * @return \static
     */
    public static function create($class)
    {
        return new static($class);
    }

    /**
     * Change the setting of onlyPublic
     *
     * @param boolean $onlyPublic
     * @return ClassSignature
     */
    public function onlyPublic($onlyPublic = true)
    {
        $this->onlyPublic = $onlyPublic;

        return $this;
    }

    /**
     * Change the setting of anonymiseParameters
     *
     * @param boolean $anonymiseParameters
     * @return ClassSignature
     */
    public function anonymiseParameters($anonymiseParameters = true)
    {
        $this->anonymiseParameters = $anonymiseParameters;

        return $this;
    }

    /**
     * Convert te case of methods and method parameters, pass false to drop conversion
     *
     * @param mixed $conversion
     * @return ClassSignature
     */
    public function convertCase($conversion = 'strtolower')
    {
        $this->caseConversion = $conversion;

        return $this;
    }

    /**
     * Generate the signature
     *
     * @return string
     */
    public function generate()
    {
        $reflectionClass = new ReflectionClass($this->class);
        $className = $reflectionClass->getShortName();

        $result = [
            $className => [
                'properties' => [],
                'methods' => [],
            ],
        ];

        $filterProperties = ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED;
        if ($this->onlyPublic) {
            $filterProperties = ReflectionProperty::IS_PUBLIC;
        }

        foreach ($reflectionClass->getProperties($filterProperties) as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();
            $access = $reflectionProperty->isPublic() ? 'public' : ($reflectionProperty->isProtected() ? 'protected' : 'unknown');

            $information = compact('access');
            if ($reflectionProperty->isStatic()) {
                $information['static'] = true;
            }
            $result[$className]['properties'][$propertyName] = $information;
        }
        uksort($result[$className]['properties'], 'strcasecmp');

        $filterMethods = ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED;
        if ($this->onlyPublic) {
            $filterMethods = ReflectionMethod::IS_PUBLIC;
        }

        foreach ($reflectionClass->getMethods($filterMethods) as $reflectionMethod) {
            $access = $reflectionMethod->isPublic() ? 'public' : ($reflectionMethod->isProtected() ? 'protected' : 'unknown');
            $parameters = [];
            $parameterCount = 0;
            foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
                $parameterCount++;
                if ($this->anonymiseParameters) {
                    $parameterName = sprintf('parameter%d', $parameterCount);
                } else {
                    $parameterName = $reflectionParameter->getName();
                }

                if ($this->caseConversion !== false) {
                    $parameterName = call_user_func($this->caseConversion, $parameterName);
                }

                $type = null;
                if (($parameterClass = $reflectionParameter->getClass()) instanceof ReflectionClass) {
                    $type = $parameterClass->getName();
                } elseif ($reflectionParameter->isArray()) {
                    $type = 'array';
                }

                $default = null;
                if ($reflectionParameter->isDefaultValueAvailable()) {
                    $default = $reflectionParameter->getDefaultValue();
                }

                $parameters[$parameterName] = [
                    'type' => $type,
                    'default' => $default,
                ];
            }

            $information = compact(
                'access',
                'parameters'
            );
            if ($reflectionMethod->isStatic()) {
                $information['static'] = true;
            }

            $methodName = $reflectionMethod->getName();
            if ($this->caseConversion !== false) {
                $methodName = call_user_func($this->caseConversion, $methodName);
            }

            $result[$className]['methods'][$methodName] = $information;
        }
        uksort($result[$className]['methods'], 'strcasecmp');

        return json_encode($result, JSON_PRETTY_PRINT);
    }
}