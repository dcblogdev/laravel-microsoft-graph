<?php

declare(strict_types=1);

namespace Dcblogdev\MsGraph\Validators;

use InvalidArgumentException;

class GraphQueryValidator extends Validator
{
    protected static array $allowedParams = [
        '$top', '$skip', '$filter', '$orderby', '$select', '$expand', '$count', '$search', '$format',
    ];
}
