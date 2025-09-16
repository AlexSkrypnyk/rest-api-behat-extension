<?php

declare(strict_types=1);

namespace Ubirak\RestApiBehatExtension\Rest;

use Ubirak\RestApiBehatExtension\ExpectationFailed;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;

class WrongResponseExpectation extends ExpectationFailed
{
    private RequestInterface $request;

    private ResponseInterface $response;

    public function __construct($message, RequestInterface $request, ResponseInterface $response, $previous = null)
    {
        $this->request = $request;
        $this->response = $response;
        parent::__construct($message, 0, $previous);
    }

    public function getContextText(): string
    {
        $formatter = new HttpExchangeFormatter($this->request, $this->response);

        return $formatter->formatFullExchange();
    }
}
