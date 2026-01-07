<?php

declare(strict_types=1);

namespace Ubirak\RestApiBehatExtension\Tests\Units\Rest;

use Http\Mock\Client;
use Http\Message\MessageFactory\GuzzleMessageFactory;
use atoum;
use Ubirak\RestApiBehatExtension\Rest\RestApiBrowser as SUT;

/**
 * @author Mikaël FIMA <mika@verylastroom.com>
 * @author Guillaume MOREL <guillaume.morel@verylastroom.com>
 */
class RestApiBrowser extends atoum
{
    /**
     * Adding headers.
     *
     * @dataProvider addHeaderDataProvider
     */
    public function testAddRequestHeader(array $addHeadersSteps, array $expectedHeaders): void
    {
        $this
            ->given(
                $httpClient = $this->mockHttpClient(200)
            )
            ->and($sut = new SUT('http://verylastroom.com', $httpClient))
        ;

        foreach ($addHeadersSteps as $addHeaderStep) {
            foreach ($addHeaderStep as $headerName => $headerValue) {
                $sut->addRequestHeader($headerName, $headerValue);
            }
        }

        $this
            ->array($sut->getRequestHeaders())->isIdenticalTo($expectedHeaders)
        ;
    }

    public function addHeaderDataProvider()
    {
        return [
            [[], []],
            [[['name' => 'value']], ['name' => 'value']],
            [[['name' => 'value'], ['name' => 'value2']], ['name' => 'value, value2']],
        ];
    }

    /**
     * Setting headers.
     *
     * @dataProvider setHeaderDataProvider
     */
    public function testSetRequestHeader(array $setHeadersSteps, array $expectedHeaders): void
    {
        $this
            ->given(
                $httpClient = $this->mockHttpClient(200)
            )
            ->and($sut = new SUT('http://verylastroom.com', $httpClient))
        ;

        foreach ($setHeadersSteps as $setHeaderStep) {
            foreach ($setHeaderStep as $headerName => $headerValue) {
                $this->and($sut->setRequestHeader($headerName, $headerValue));
            }
        }

        $this
            ->array($sut->getRequestHeaders())->isIdenticalTo($expectedHeaders)
        ;
    }

    public function setHeaderDataProvider()
    {
        return [
            [[], []],
            [[['name' => 'value']], ['name' => 'value']],
            [[['name' => 'value'], ['name' => 'value2']], ['name' => 'value2']],
        ];
    }

    /**
     * @dataProvider urlWithSlashesProvider
     *
     * @param string $baseUrl
     * @param string $stepUrl
     * @param string $expectedUrl
     */
    public function testCreateRequestWithSlashesToClean($baseUrl, $stepUrl, $expectedUrl): void
    {
        // Given
        $mockHttpClient = $this->mockHttpClient(200);
        $restApiContext = new SUT($baseUrl, $mockHttpClient);
        // When
        $restApiContext->sendRequest('GET', $stepUrl);
        // Then
        $request = $restApiContext->getRequest();
        $this->castToString($request->getUri())->isEqualTo($expectedUrl);
    }

    public function urlWithSlashesProvider()
    {
        return [
            [ // Trim right + left
                'baseUrl' => 'http://verylastroom.com/',
                'stepUrl' => '/contact/',
                'expectedUrl' => 'http://verylastroom.com/contact/',
            ],
            [ // Trim left
                'baseUrl' => 'http://verylastroom.com',
                'stepUrl' => '/contact/',
                'expectedUrl' => 'http://verylastroom.com/contact/',
            ],
            [ // Trim right
                'baseUrl' => 'http://verylastroom.com/',
                'stepUrl' => 'contact/',
                'expectedUrl' => 'http://verylastroom.com/contact/',
            ],
            [ // Add missing slash
                'baseUrl' => 'http://verylastroom.com',
                'stepUrl' => 'contact/',
                'expectedUrl' => 'http://verylastroom.com/contact/',
            ],
        ];
    }

    /**
     * @dataProvider responseDataProvider
     *
     * @param int   $statusCode
     */
    public function testGetReturnTheResponseWeExpected($statusCode, array $responseHeaders): void
    {
        // Given
        $mockHttpClient = $this->mockHttpClient($statusCode, $responseHeaders);

        $restApiContext = new SUT('http://verylastroom.com', $mockHttpClient);

        // When
        $restApiContext->sendRequest('GET', 'http://verylastroom.com/');

        // Then
        $response = $restApiContext->getResponse();
        $intersect = array_intersect_key($responseHeaders, $response->getHeaders());

        $this->array($responseHeaders)->isEqualTo($intersect);
    }

    public function responseDataProvider()
    {
        return [
            [
                'statusCode' => 200,
                'responseHeaders' => [
                    'name' => 'value',
                ],
            ],
            [
                'statusCode' => 400,
                'responseHeaders' => [
                    'name1' => 'value1',
                    'name2' => 'value2',
                ],
            ],
        ];
    }

    /**
     * @param string $baseUrl
     * @param int    $responseStatusCode
     *
     * @return \Ivory\HttpAdapter\HttpAdapterInterface
     */
    private function mockHttpClient($responseStatusCode, array $headers = []): Client
    {
        $mockHttpClient = new Client();
        $messageFactory = new GuzzleMessageFactory();
        $mockHttpClient->addResponse(
            $messageFactory->createResponse(
                $responseStatusCode,
                null,
                $headers
            )
        );

        return $mockHttpClient;
    }
}
