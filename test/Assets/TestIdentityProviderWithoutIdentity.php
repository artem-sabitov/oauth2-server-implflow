<?php

namespace OAuth2Test\Grant\Implicit\Assets;

use OAuth2\Grant\Implicit\IdentityInterface;

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
