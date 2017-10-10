<?php

namespace OAuth2\Grant\Implicit;

class Messages
{
    /**
     * @var array
     */
    protected $messages;

    /**
     * @param string $message
     * @return Messages
     */
    public function addErrorMessage(string $message): Messages
    {
        $this->messages[] = $message;

        return $this;
    }
}