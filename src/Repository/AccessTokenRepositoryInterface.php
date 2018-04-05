<?php

declare(strict_types=1);

namespace OAuth2\Repository;

use OAuth2\Token\AccessToken;

interface AccessTokenRepositoryInterface
{
    public function write(AccessToken $token) : void;

    public function find(string $token) : ?AccessToken;
}
