<?php

namespace OAuth2\Grant\Implicit\Provider;

use OAuth2\Grant\Implicit\IdentityInterface;

interface IdentityProviderInterface
{
    /**
     * @return IdentityInterface|null
     */
    public function getIdentity();

    /**
     * @return boolean
     */
    public function hasIdentity();
}