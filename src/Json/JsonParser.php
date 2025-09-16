<?php

declare(strict_types=1);

namespace Ubirak\RestApiBehatExtension\Json;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use JsonSchema\Validator;
use JsonSchema\SchemaStorage;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Uri\UriResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;

class JsonParser
{
    private $evaluationMode;

    private PropertyAccessorInterface $propertyAccessor;

    public function __construct($evaluationMode)
    {
        $this->evaluationMode = $evaluationMode;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidIndex()
            ->getPropertyAccessor()
        ;
    }

    public function evaluate(Json $json, $expression)
    {
        if ($this->evaluationMode === 'javascript') {
            $expression = str_replace('->', '.', $expression);
        }

        try {
            return $json->read($expression, $this->propertyAccessor);
        } catch (\Exception $exception) {
            throw new \Exception(sprintf('Failed to evaluate expression "%s"', $expression), 0, $exception);
        }
    }

    public function validate(Json $json, JsonSchema $schema): bool
    {
        return $schema->validate($json, new Validator(), new SchemaStorage(new UriRetriever(), new UriResolver()));
    }
}
