<?php

namespace OAuth2\Grant\Implicit;

class Client
{
    /**
     * @return string
     */
    public function getClientId()
    {
        return '';
    }

    /**
     * @return array
     */
    public function getAvailableRedirectUri()
    {
        return [];
    }
}