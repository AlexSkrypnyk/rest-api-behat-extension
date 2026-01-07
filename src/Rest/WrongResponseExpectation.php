<?php

declare(strict_types=1);

namespace Ubirak\RestApiBehatExtension\Rest;

use Ubirak\RestApiBehatExtension\ExpectationFailed;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;

class WrongResponseExpectation extends ExpectationFailed
{
    public function __construct(
        $message,
        private readonly RequestInterface $request,
        private readonly ResponseInterface $response,
        $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getContextText(): string
    {
        $formatter = new HttpExchangeFormatter($this->request, $this->response);

        return $formatter->formatFullExchange();
    }
}
