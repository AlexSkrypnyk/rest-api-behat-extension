<?php

declare(strict_types=1);

namespace Ubirak\RestApiBehatExtension\Json;

use Ubirak\RestApiBehatExtension\ExpectationFailed;

class WrongJsonExpectation extends ExpectationFailed
{
    private Json $json;

    public function __construct($message, Json $json, $previous = null)
    {
        $this->json = $json;
        parent::__construct($message, 0, $previous);
    }

    public function getContextText()
    {
        return $this->json->encode(true);
    }
}
