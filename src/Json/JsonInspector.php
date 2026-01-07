<?php

declare(strict_types=1);

namespace Ubirak\RestApiBehatExtension\Json;

class JsonInspector
{
    public function __construct(
        private readonly JsonStorage $jsonStorage,
        private readonly JsonParser $jsonParser,
        private readonly JsonSearcher $jsonSearcher,
    ) {
    }

    public function readJsonNodeValue($jsonNodeExpression)
    {
        return $this->jsonParser->evaluate(
            $this->readJson(),
            $jsonNodeExpression
        );
    }

    public function searchJsonPath($pathExpression)
    {
        return $this->jsonSearcher->search($this->readJson(), $pathExpression);
    }

    public function validateJson(JsonSchema $jsonSchema): void
    {
        $this->jsonParser->validate(
            $this->readJson(),
            $jsonSchema
        );
    }

    public function readJson(): Json
    {
        return $this->jsonStorage->readJson();
    }

    public function writeJson($jsonContent): void
    {
        $this->jsonStorage->writeRawContent($jsonContent);
    }
}
