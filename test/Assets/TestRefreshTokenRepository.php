<?php

namespace OAuth2Test\Assets;

use OAuth2\Repository\AccessTokenRepositoryInterface;
use OAuth2\Repository\RefreshTokenRepositoryInterface;
use OAuth2\Token\RefreshToken;

class TestRefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    /**
     * @var null|array
     */
    private $repo;

    public function write(RefreshToken $token): RefreshToken
    {
        return $token;
    }

    public function find(string $token): ?RefreshToken
    {
        if (isset($this->repo[$token]) === false) {
            return null;
        }

        return $this->repo[$token];
    }
}
