<?php

declare(strict_types=1);

namespace Ubirak\RestApiBehatExtension\Rest;

use Ubirak\RestApiBehatExtension\Html\Form;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tolerance\Operation\Callback;
use Tolerance\Operation\Runner\RetryOperationRunner;
use Tolerance\Operation\Runner\CallbackOperationRunner;
use Tolerance\Waiter\SleepWaiter;
use Tolerance\Waiter\TimeOut;
use GuzzleHttp\Psr7\MultipartStream;

class RestApiBrowser
{
    /** @var ClientInterface */
    private $httpClient;

    /** @var RequestInterface */
    private $request;

    /** @var ResponseInterface */
    private $response;

    private array $requestHeaders = [];

    private ?ResponseStorage $responseStorage = null;

    /** @var string */
    private $host;

    /** @var RequestFactoryInterface */
    private $requestFactory;

    /** @var StreamFactoryInterface */
    private $streamFactory;

    /**
     * @param string $host
     */
    public function __construct($host, ?ClientInterface $httpClient = null)
    {
        $this->host = $host;
        $this->httpClient = $httpClient ?: Psr18ClientDiscovery::find();
        $this->requestFactory = Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = Psr17FactoryDiscovery::findStreamFactory();
    }

    /**
     * Allow to override the httpClient to use yours with specific middleware for example.
     */
    public function useHttpClient(ClientInterface $httpClient): void
    {
        $this->httpClient = $httpClient;
    }

    public function enableResponseStorage(ResponseStorage $responseStorage): void
    {
        $this->responseStorage = $responseStorage;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    public function setResponse(ResponseInterface $response): void
    {
        $this->response = $response;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getRequestHeaders(): array
    {
        return $this->requestHeaders;
    }

    /**
     * @param string       $uri
     * @param string|array $body
     */
    public function sendRequest(string $method, $uri, $body = null): void
    {
        if (false === $this->hasHost($uri)) {
            $uri = rtrim($this->host, '/').'/'.ltrim($uri, '/');
        }

        if (is_array($body)) {
            $html = new Form($body);
            $body = $html->getBody();
            $this->setRequestHeader('Content-Type', $html->getContentTypeHeaderValue());
        }

        $this->request = $this->requestFactory->createRequest($method, $uri);
        foreach ($this->requestHeaders as $keyHeader => $valueHeader) {
            $this->request = $this->request->withHeader($keyHeader, $valueHeader);
        }
        if (null !== $body) {
            if ($body instanceof MultipartStream) {
                $this->request = $this->request->withBody($body);
            } else {
                $this->request = $this->request->withBody($this->streamFactory->createStream($body));
            }
        }

        $this->response = $this->httpClient->sendRequest($this->request);
        $this->requestHeaders = [];

        if ($this->responseStorage instanceof ResponseStorage) {
            $this->responseStorage->writeRawContent((string) $this->response->getBody());
        }
    }

    public function sendRequestUntil(string $method, $uri, $body, callable $assertion, $maxExecutionTime = 10): void
    {
        $runner = new RetryOperationRunner(
            new CallbackOperationRunner(),
            new TimeOut(new SleepWaiter(), $maxExecutionTime)
        );
        $restApiBrowser = $this;
        $runner->run(new Callback(function () use ($restApiBrowser, $method, $uri, $body, $assertion) {
            $restApiBrowser->sendRequest($method, $uri, $body);

            return $assertion();
        }));
    }

    /**
     * @param string $name
     */
    public function setRequestHeader($name, string $value): void
    {
        $this->removeRequestHeader($name);
        $this->addRequestHeader($name, $value);
    }

    /**
     * @param string $name
     */
    public function addRequestHeader($name, string $value): void
    {
        $name = strtolower($name);
        if (isset($this->requestHeaders[$name])) {
            $this->requestHeaders[$name] .= ', '.$value;
        } else {
            $this->requestHeaders[$name] = $value;
        }
    }

    /**
     * @param string $headerName
     */
    private function removeRequestHeader($headerName): void
    {
        $headerName = strtolower($headerName);
        if (array_key_exists($headerName, $this->requestHeaders)) {
            unset($this->requestHeaders[$headerName]);
        }
    }

    /**
     * @param string $uri
     */
    private function hasHost($uri): bool
    {
        return false !== strpos($uri, '://');
    }
}
