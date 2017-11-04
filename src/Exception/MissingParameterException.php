<?php

namespace OAuth2\Grant\Implicit\Exception;

use \InvalidArgumentException;

class MissingParameterException extends InvalidArgumentException
{
    /**
     * @var string
     */
    protected static $messageTemplate = 'You must include a valid \'%s\' parameter';

    /**
     * @param string $parameterName
     * @return MissingParameterException
     */
    public static function create(string $parameterName)
    {
        return new self(sprintf(
            self::$messageTemplate,
            $parameterName
        ));
    }
}
