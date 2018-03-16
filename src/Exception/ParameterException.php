<?php

namespace OAuth2\Exception;

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
     * @var array
     */
    protected $messages;

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

    public static function create(array $messages): ParameterException
    {
        return (new ParameterException())->withMessages($messages);
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        if ($this->messages === null) {
            $this->messages = [$this->getMessage()];
        }

        return $this->messages;
    }

    public function withMessages(array $messages)
    {
        $this->messages = $messages;

        return $this;
    }
}
