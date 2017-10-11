<?php

namespace OAuth2\Grant\Implicit;

interface ClientInterface
{
    /**
     * @return string
     */
    public function getClientId();

    /**
     * @return array
     */
    public function getListAvailableRedirectUri();
}