<?php

declare(strict_types=1);

namespace Dcblogdev\MsGraph\Validators;

use InvalidArgumentException;

class EmailFolderStoreValidator extends Validator
{
    protected static array $allowedParams = [
        'displayName', 'isHidden',
    ];

    public static function validate(array $params): array
    {
        $validParams = parent::validate($params);

        if (isset($validParams['displayName']) && ! is_string($validParams['displayName'])) {
            throw new InvalidArgumentException('The displayName must be a string.');
        }

        if (isset($validParams['isHidden']) && ! is_bool($validParams['isHidden'])) {
            throw new InvalidArgumentException('The isHidden must be a boolean.');
        }

        return $validParams;
    }
}
