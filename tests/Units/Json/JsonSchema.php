<?php

declare(strict_types=1);

namespace Ubirak\RestApiBehatExtension\Tests\Units\Json;

use Ubirak\RestApiBehatExtension\Json\Json;
use mock\JsonSchema\Validator;
use mock\JsonSchema\SchemaStorage;
use atoum;
use Ubirak\RestApiBehatExtension\Json\JsonSchema as SUT;

class JsonSchema extends atoum
{
    public function testShouldValidateCorrectJson(): void
    {
        $this
            ->given(
                $sut = new SUT('schema.json'),
                $json = new Json('{"foo":"bar"}'),
                $validator = new Validator(),
                $validator->getMockController()->check = true,
                $this->mockGenerator->orphanize('__construct'),
                $schemaStorage = new SchemaStorage(),
                $schemaStorage->getMockController()->resolveRef = 'mySchema'
            )
            ->when(
                $result = $sut->validate($json, $validator, $schemaStorage)
            )
                ->mock($validator)
                    ->call('check')
                    ->withArguments(json_decode('{"foo":"bar"}'), 'mySchema')
                    ->once()

                ->boolean($result)
                    ->isTrue()
        ;
    }

    public function testShouldThrowExceptionForIncorrectJson(): void
    {
        $this
            ->given(
                $sut = new SUT('schema.json'),
                $json = new Json('{}'),
                $validator = new Validator(),
                $validator->getMockController()->check = false,
                $validator->getMockController()->getErrors = [
                    ['property' => 'foo', 'message' => 'invalid'],
                    ['property' => 'bar', 'message' => 'not found'],
                ],
                $this->mockGenerator->orphanize('__construct'),
                $schemaStorage = new SchemaStorage(),
                $schemaStorage->getMockController()->resolveRef = 'mySchema'
            )
            ->exception(function () use ($sut, $json, $validator, $schemaStorage): void {
                $sut->validate($json, $validator, $schemaStorage);
            })
                ->hasMessage(
                    <<<'ERROR'
JSON does not validate. Violations:
  - [foo] invalid
  - [bar] not found

ERROR
                )
        ;
    }
}
