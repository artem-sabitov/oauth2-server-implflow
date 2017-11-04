<?php

namespace OAuth2\Grant\Implicit\Exception;

use \OutOfRangeException;

class InvalidParameterException extends OutOfRangeException
{
    /**
     * @var string
     */
    protected static $messageTemplate = 'Invalid \'%s\' %s';

    /**
     * @param string $parameterName
     * @param string $value
     * @return InvalidParameterException
     */
    public static function create(string $parameterName, string $value)
    {
        return new self(sprintf(
            self::$messageTemplate,
            $value,
            $parameterName
        ));
    }
}
