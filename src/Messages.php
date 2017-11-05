<?php

namespace OAuth2\Grant\Implicit;

class Messages
{
    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->messages;
    }

    /**
     * @param string $key
     * @param string $message
     * @return Messages
     */
    public function addErrorMessage(string $key, string $message): Messages
    {
        $this->messages[$key] = $message;

        return $this;
    }
}
