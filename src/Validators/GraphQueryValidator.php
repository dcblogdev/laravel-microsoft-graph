<?php

declare(strict_types=1);

namespace Dcblogdev\MsGraph\Validators;

class GraphQueryValidator extends Validator
{
    protected static array $allowedParams = [
        '$top', '$skip', '$filter', '$orderby', '$select', '$expand', '$count', '$search', '$format',
    ];
}
