<?php

declare(strict_types=1);

namespace Dcblogdev\MsGraph\Validators;

use InvalidArgumentException;

class EmailFolderUpdateValidator extends Validator
{
    protected static array $allowedParams = [
        'displayName',
    ];

    public static function validate(array $params): array
    {
        $validParams = parent::validate($params);

        if (isset($validParams['displayName']) && ! is_string($validParams['displayName'])) {
            throw new InvalidArgumentException('The displayName must be a string.');
        }

        return $validParams;
    }
}
