<?php

declare(strict_types=1);

namespace Ubirak\RestApiBehatExtension\Tests\Units\Json;

use Ubirak\RestApiBehatExtension\Json\Json;
use atoum;

class WrongJsonExpectation extends atoum
{
    public function testItDisplayPrettyJsonWhenCastToString(): void
    {
        $this
            ->given(
                $json = new Json('{"foo":"bar"}'),
                $this->newTestedInstance('Error', $json)
            )
            ->when(
                $result = $this->testedInstance->__toString()
            )
            ->then
                ->string($result)
                    ->contains(<<<'EOF'
|  {
|      "foo": "bar"
|  }
EOF
                    )
        ;
    }
}
