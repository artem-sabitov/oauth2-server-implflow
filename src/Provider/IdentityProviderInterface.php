<?php

namespace OAuth2\Provider;

use OAuth2\IdentityInterface;

interface IdentityProviderInterface
{
    /**
     * @return IdentityInterface
     * @throws \InvalidArgumentException
     */
    public function getIdentity(): IdentityInterface;

    /**
     * @return boolean
     */
    public function hasIdentity(): bool;
}
