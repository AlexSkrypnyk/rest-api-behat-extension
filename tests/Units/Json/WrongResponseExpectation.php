<?php

declare(strict_types=1);

namespace Ubirak\RestApiBehatExtension\Tests\Units\Rest;

use mock\Psr\Http\Message\UriInterface;
use mock\Psr\Http\Message\RequestInterface;
use mock\Psr\Http\Message\StreamInterface;
use mock\Psr\Http\Message\ResponseInterface;
use atoum;

class WrongResponseExpectation extends atoum
{
    public function testItDisplayPrettyResponseWhenCastToString(): void
    {
        $this
            ->given(
                $uri = new UriInterface(),
                $this->calling($uri)->__toString = 'http://test.com/foo',
                $request = new RequestInterface(),
                $this->calling($request)->getMethod = 'GET',
                $this->calling($request)->getUri = $uri,
                $stream = new StreamInterface(),
                $this->calling($stream)->__toString = '{"status":"ok"}',
                $response = new ResponseInterface(),
                $this->calling($response)->getStatusCode = 200,
                $this->calling($response)->getReasonPhrase = 'OK',
                $this->calling($response)->getHeaders = ['Content-Type' => 'application/json'],
                $this->calling($response)->getBody = $stream,
                $this->newTestedInstance('Error', $request, $response)
            )
            ->when(
                $result = $this->testedInstance->__toString()
            )
            ->then
                ->string($result)
                    ->contains(<<<'EOF'
|  GET http://test.com/foo :
|  200 OK
|  Content-Type: application/json
|  
|  {"status":"ok"}
EOF
                    )
        ;
    }
}
