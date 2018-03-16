<?php

namespace OAuth2Test\Assets;

use OAuth2\IdentityInterface;

class TestIdentityProviderWithoutIdentity extends TestSuccessIdentityProvider
{
    public function hasIdentity(): bool
    {
        return false;
    }

    public function getIdentity(): IdentityInterface
    {
        return null;
    }
}
