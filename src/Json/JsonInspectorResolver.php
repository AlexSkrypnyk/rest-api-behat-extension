<?php

declare(strict_types=1);

namespace Ubirak\RestApiBehatExtension\Json;

use Behat\Behat\Context\Argument\ArgumentResolver;

class JsonInspectorResolver implements ArgumentResolver
{
    private JsonInspector $jsonInspector;

    public function __construct(JsonInspector $jsonInspector)
    {
        $this->jsonInspector = $jsonInspector;
    }

    public function resolveArguments(\ReflectionClass $classReflection, array $arguments)
    {
        $constructor = $classReflection->getConstructor();
        if ($constructor === null) {
            return $arguments;
        }

        $parameters = $constructor->getParameters();
        foreach ($parameters as $parameter) {
            if ($parameter->getType() instanceof \ReflectionType &&
                ($parameter->getType()->getName()) === JsonInspector::class
            ) {
                $arguments[$parameter->name] = $this->jsonInspector;
            }
        }

        return $arguments;
    }
}
