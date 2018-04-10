<?php

namespace OAuth2Test\Assets;

use OAuth2\Repository\AccessTokenRepositoryInterface;
use OAuth2\Token\AccessToken;

class TestAccessTokenRepository implements AccessTokenRepositoryInterface
{
    /**
     * @var null|array
     */
    private $repo;

    public function write(AccessToken $token): AccessToken
    {
        return $token;
    }

    public function find(string $token): ?AccessToken
    {
        if (isset($this->repo[$token]) === false) {
            return null;
        }

        return $this->repo[$token];
    }
}
