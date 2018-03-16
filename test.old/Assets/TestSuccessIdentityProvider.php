<?php

namespace OAuth2Test\Assets;

use OAuth2\IdentityInterface;
use OAuth2\Provider\IdentityProviderInterface;

class TestSuccessIdentityProvider implements IdentityProviderInterface
{
    protected $identity;

    public function __construct()
    {
        $this->identity = new Identity();
    }

    /**
     * @return Identity
     */
    public function getIdentity(): IdentityInterface
    {
        return $this->identity;
    }

    /**
     * @return bool
     */
    public function hasIdentity(): bool
    {
        return $this->identity !== null;
    }
}
