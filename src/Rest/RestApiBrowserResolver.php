<?php

declare(strict_types=1);

namespace Ubirak\RestApiBehatExtension\Rest;

use Behat\Behat\Context\Argument\ArgumentResolver;

class RestApiBrowserResolver implements ArgumentResolver
{
    private RestApiBrowser $restApiBrowser;

    public function __construct(RestApiBrowser $restApiBrowser)
    {
        $this->restApiBrowser = $restApiBrowser;
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
                ($parameter->getType()->getName()) === RestApiBrowser::class
            ) {
                $arguments[$parameter->name] = $this->restApiBrowser;
            }
        }

        return $arguments;
    }
}
