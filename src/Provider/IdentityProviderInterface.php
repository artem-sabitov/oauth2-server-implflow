<?php

namespace OAuth2\Grant\Implicit\Provider;

use OAuth2\Grant\Implicit\IdentityInterface;

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
