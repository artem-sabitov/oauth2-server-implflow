<?php

namespace OAuth2\Grant\Implicit\Adapter;

use OAuth2\Grant\Implicit\Parameter\RedirectUriInterface;
use OAuth2\Grant\Implicit\Parameter\ResponseTypeInterface;
use OAuth2\Grant\Implicit\Parameter\ClientIdInterface;

interface AdapterInterface
{
    /**
     * @return string|ClientIdInterface
     */
    public function getClientId();

    /**
     * @return string|RedirectUriInterface
     */
    public function getRedirectUri();

    /**
     * @return string|ResponseTypeInterface
     */
    public function getResponseType();
}