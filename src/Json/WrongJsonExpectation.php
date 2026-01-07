<?php

declare(strict_types=1);

namespace Ubirak\RestApiBehatExtension\Json;

use Ubirak\RestApiBehatExtension\ExpectationFailed;

class WrongJsonExpectation extends ExpectationFailed
{
    public function __construct($message, private readonly Json $json, $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    public function getContextText()
    {
        return $this->json->encode(true);
    }
}
