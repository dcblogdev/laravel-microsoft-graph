<?php

declare(strict_types=1);

namespace Dcblogdev\MsGraph\Validators;

use InvalidArgumentException;

class Validator
{
    protected static array $allowedParams = [];

    /**
     * Validate and filter parameters.
     *
     * @param  array  $params  parameters to validate.
     * @return array Valid parameters.
     *
     * @throws InvalidArgumentException If invalid parameters are present.
     */
    public static function validate(array $params): array
    {
        $allowedParams = static::$allowedParams;

        // Filter out invalid parameters
        $validParams = array_intersect_key($params, array_flip($allowedParams));

        // Identify invalid parameters
        $invalidParams = array_diff_key($params, $validParams);
        if (! empty($invalidParams)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid parameters: %s. Allowed parameters: %s.',
                implode(', ', array_keys($invalidParams)),
                implode(', ', $allowedParams)
            ));
        }

        return $validParams;
    }
}
