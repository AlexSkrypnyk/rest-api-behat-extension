<?php

declare(strict_types=1);

namespace Ubirak\RestApiBehatExtension\Tests\Units\Json;

use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use atoum;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Ubirak\RestApiBehatExtension\Json\Json as SUT;

class Json extends atoum
{
    public function testShouldNotDecodeInvalidJson(): void
    {
        $this
            ->exception(function (): void {
                $sut = new SUT('{{json');
            })
                ->hasMessage('The string "{{json" is not valid json')
        ;
    }

    public function testShouldDecodeValidJson(): void
    {
        try {
            $this
                ->given(
                    $hasException = false
                )
                ->when(
                    new SUT('{"foo": "bar"}')
                )
            ;
        } catch (\Exception $exception) {
            $hasException = true;
        }

        $this->boolean($hasException)->isFalse();
    }

    public function testShouldEncodeValidJson(): void
    {
        $this
            ->given(
                $content = '{"foo":"bar"}'
            )
            ->when(
                $sut = new SUT($content)
            )
            ->then
                ->castToString($sut)
                    ->isEqualTo($content)
        ;
    }

    public function testShouldNotReadInvalidExpression(): void
    {
        $this
            ->given(
                $accessor = PropertyAccess::createPropertyAccessor(),
                $sut = new SUT('{"foo":"bar"}')
            )
            ->exception(function () use ($sut, $accessor): void {
                $sut->read('jeanmarc', $accessor);
            })
                ->isInstanceOf(NoSuchPropertyException::class)
        ;
    }

    public function testShouldReadValidExpression(): void
    {
        $stringAsserterFunc = class_exists('mageekguy\\atoum\\asserters\\phpString') ? 'phpString' : 'string';
        $this
            ->given(
                $accessor = PropertyAccess::createPropertyAccessor(),
                $sut = new SUT('{"foo":"bar"}')
            )
            ->when(
                $result = $sut->read('foo', $accessor)
            )
                ->$stringAsserterFunc($result)
                    ->isEqualTo('bar')
        ;
    }
}
