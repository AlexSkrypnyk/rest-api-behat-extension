<?php

declare(strict_types=1);

namespace Ubirak\RestApiBehatExtension\Rest;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;

class HttpExchangeFormatter
{
    public function __construct(
        private readonly ?RequestInterface $request = null,
        private readonly ?ResponseInterface $response = null,
    ) {
    }

    public function formatRequest(): string
    {
        if (!$this->request instanceof RequestInterface) {
            throw new \LogicException('You should send a request before printing it.');
        }

        return sprintf(
            "%s %s :\n%s%s\n",
            $this->request->getMethod(),
            $this->request->getUri(),
            $this->getRawHeaders($this->request->getHeaders()),
            $this->request->getBody()
        );
    }

    public function formatFullExchange(): string
    {
        if (!$this->request instanceof RequestInterface || !$this->response instanceof ResponseInterface) {
            throw new \LogicException('You should send a request and store its response before printing them.');
        }

        return sprintf(
            "%s %s :\n%s %s\n%s%s\n",
            $this->request->getMethod(),
            $this->request->getUri()->__toString(),
            $this->response->getStatusCode(),
            $this->response->getReasonPhrase(),
            $this->getRawHeaders($this->response->getHeaders()),
            $this->response->getBody()
        );
    }

    private function getRawHeaders(array $headers): string
    {
        $rawHeaders = '';
        foreach ($headers as $key => $value) {
            $rawHeaders .= sprintf("%s: %s\n", $key, is_array($value) ? implode(', ', $value) : $value);
        }

        return $rawHeaders . "\n";
    }
}
