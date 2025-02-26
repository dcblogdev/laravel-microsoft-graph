<?php

declare(strict_types=1);

namespace Dcblogdev\MsGraph\Validators;

use InvalidArgumentException;

class GraphQueryValidator
{
    /**
     * Allowed query parameters for Microsoft Graph API requests.
     */
    protected static array $allowedParams = [
        '$top', '$skip', '$filter', '$orderby', '$select', '$expand', '$count', '$search', '$format',
    ];

    /**
     * Validate and filter query parameters.
     *
     * @param  array  $params  Query parameters to validate.
     * @return array Valid query parameters.
     *
     * @throws InvalidArgumentException If invalid parameters are present.
     */
    public static function validate(array $params): array
    {
        // Filter out invalid parameters
        $validParams = array_intersect_key($params, array_flip(self::$allowedParams));

        // Identify invalid parameters
        $invalidParams = array_diff_key($params, $validParams);
        if (! empty($invalidParams)) {
            throw new InvalidArgumentException(sprintf(
                "Invalid query parameters: %s. Allowed parameters: %s.",
                implode(', ', array_keys($invalidParams)),
                implode(', ', self::$allowedParams)
            ));
        }

        return $validParams;
    }
}
