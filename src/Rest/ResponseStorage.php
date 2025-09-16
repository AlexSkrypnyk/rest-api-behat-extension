<?php

declare(strict_types=1);

namespace Ubirak\RestApiBehatExtension\Rest;

interface ResponseStorage
{

    public function writeRawContent($rawContent): void;
}
