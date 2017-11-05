<?php

namespace OAuth2\Grant\Implicit\Exception;

use \InvalidArgumentException;

class ParameterException extends InvalidArgumentException
{
    const INVALID_PARAMETER = 1;
    const MISSING_PARAMETER = 2;

    /**
     * @var string
     */
    protected static $messageTemplates = [
        self::INVALID_PARAMETER => 'Invalid \'%s\' parameter',
        self::MISSING_PARAMETER => 'Required parameter \'%s\' missing',
    ];

    /**
     * @param string $parameterName
     * @param string $value
     * @return ParameterException
     */
    public static function createInvalidParameter(string $parameterName)
    {
        return new self(sprintf(
            self::$messageTemplates[self::INVALID_PARAMETER],
            $parameterName
        ));
    }

    public static function createMissingParameter(string $parameterName)
    {
        return new self(sprintf(
            self::$messageTemplates[self::MISSING_PARAMETER],
            $parameterName
        ));
    }
}
