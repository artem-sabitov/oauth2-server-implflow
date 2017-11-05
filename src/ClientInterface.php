<?php

namespace OAuth2\Grant\Implicit;

interface ClientInterface
{
    /**
     * @return string
     */
    public function getClientId(): string;

    /**
     * @return string
     */
    public function getRedirectUri(): string;
}
