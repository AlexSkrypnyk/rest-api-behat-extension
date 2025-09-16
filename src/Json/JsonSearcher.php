<?php

declare(strict_types=1);

namespace Ubirak\RestApiBehatExtension\Json;

use JmesPath\Env;

/**
 * Use https://github.com/jmespath/jmespath.php
 * alternative could be https://github.com/FlowCommunications/JSONPath.
 */
class JsonSearcher
{
    public function search(Json $json, $pathExpression)
    {
        return Env::search($pathExpression, $json->getRawContent());
    }
}
