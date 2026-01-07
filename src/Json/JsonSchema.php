<?php

declare(strict_types=1);

namespace Ubirak\RestApiBehatExtension\Json;

use JsonSchema\SchemaStorage;
use JsonSchema\Validator;

class JsonSchema
{
    /**
     * @param string $filename
     */
    public function __construct(private $filename)
    {
    }

    public function validate(Json $json, Validator $validator, SchemaStorage $schemaStorage): bool
    {
        $schema = $schemaStorage->resolveRef('file://'.realpath($this->filename));
        $data = $json->getRawContent();

        $validator->check($data, $schema);

        if (!$validator->isValid()) {
            $msg = 'JSON does not validate. Violations:'.PHP_EOL;
            foreach ($validator->getErrors() as $error) {
                $msg .= sprintf('  - [%s] %s'.PHP_EOL, $error['property'], $error['message']);
            }
            throw new \Exception($msg);
        }

        return true;
    }
}
