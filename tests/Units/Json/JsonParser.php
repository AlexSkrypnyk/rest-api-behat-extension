<?php

declare(strict_types=1);

namespace Ubirak\RestApiBehatExtension\Tests\Units\Json;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use mock\Ubirak\RestApiBehatExtension\Json\Json;
use mock\Ubirak\RestApiBehatExtension\Json\JsonSchema;
use atoum;
use JsonSchema\Validator;
use Ubirak\RestApiBehatExtension\Json\JsonParser as SUT;

class JsonParser extends atoum
{
    private function getPropertyAccessor(): PropertyAccessorInterface
    {
        return PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidIndex()
            ->getPropertyAccessor();
    }
    public function testShouldReadJson(): void
    {
        $this
            ->given(
                $json = new Json('{}'),
                $json->getMockController()->read = 'foobar'
            )
            ->and(
                $sut = new SUT('mode')
            )
            ->when(
                $result = $sut->evaluate($json, 'foo.bar')
            )
            ->then
                ->variable($result)
                    ->isEqualTo('foobar')

                ->mock($json)
                    ->call('read')
                    ->withArguments('foo.bar', $this->getPropertyAccessor())
                    ->once()
        ;
    }

    public function testShouldFailIfJsonReadingFail(): void
    {
        $this
            ->given(
                $json = new Json('{}'),
                $json->getMockController()->read->throw = new \Exception()
            )
            ->and(
                $sut = new SUT('mode')
            )
                ->exception(function () use ($json, $sut): void {
                    $sut->evaluate($json, 'foo.bar');
                })
                    ->hasMessage('Failed to evaluate expression "foo.bar"')
        ;
    }

    public function testShouldConvertExpressionIfJavascriptMode(): void
    {
        $this
            ->given(
                $json = new Json('{}'),
                $json->getMockController()->read = 'foobar'
            )
            ->and(
                $sut = new SUT('javascript')
            )
            ->when(
                $result = $sut->evaluate($json, 'foo->bar')
            )
            ->then
                ->variable($result)
                    ->isEqualTo('foobar')

                ->mock($json)
                    ->call('read')
                    ->withArguments('foo.bar', $this->getPropertyAccessor())
                    ->once()
        ;
    }

    public function testShouldNoConvertExpressionIfNoJavascriptMode(): void
    {
        $this
            ->given(
                $json = new Json('{}'),
                $json->getMockController()->read = 'foobar'
            )
            ->and(
                $sut = new SUT('foo')
            )
            ->when(
                $result = $sut->evaluate($json, 'foo->bar')
            )
            ->then
                ->variable($result)
                    ->isEqualTo('foobar')

                ->mock($json)
                    ->call('read')
                    ->withArguments('foo->bar', $this->getPropertyAccessor())
                    ->once()
        ;
    }

    public function testShouldValidJsonThroughItsSchema(): void
    {
        $this
            ->given(
                $json = new Json('{}'),
                $schema = new JsonSchema('{}'),
                $schema->getMockController()->validate = 'foobar',
                $sut = new SUT('foo')
            )
            ->when(
                $result = $sut->validate($json, $schema)
            )
            ->then
                ->variable($result)
                    ->isEqualTo('foobar')

                ->mock($schema)
                    ->call('validate')
                    ->withArguments($json, new Validator())
                    ->once()
        ;
    }
}
