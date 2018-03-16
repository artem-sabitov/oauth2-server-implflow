<?php

namespace OAuth2;

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
