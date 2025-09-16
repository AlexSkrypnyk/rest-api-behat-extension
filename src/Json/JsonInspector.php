<?php

declare(strict_types=1);

namespace Ubirak\RestApiBehatExtension\Json;

class JsonInspector
{
    private JsonParser $jsonParser;

    private JsonStorage $jsonStorage;

    private JsonSearcher $jsonSearcher;

    public function __construct(JsonStorage $jsonStorage, JsonParser $jsonParser, JsonSearcher $jsonSearcher)
    {
        $this->jsonParser = $jsonParser;
        $this->jsonStorage = $jsonStorage;
        $this->jsonSearcher = $jsonSearcher;
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
