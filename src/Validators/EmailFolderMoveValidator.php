<?php

declare(strict_types=1);

namespace Dcblogdev\MsGraph\Validators;

use InvalidArgumentException;

class EmailFolderMoveValidator extends Validator
{
    protected static array $allowedParams = [
        'destinationId',
    ];

    public static function validate(array $params): array
    {
        $validParams = parent::validate($params);

        if (isset($validParams['destinationId']) && ! is_string($validParams['destinationId'])) {
            throw new InvalidArgumentException('The destinationId must be a string.');
        }

        return $validParams;
    }
}
