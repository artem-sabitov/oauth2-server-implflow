<?php

declare(strict_types=1);

namespace OAuth2\Repository;

use OAuth2\Token\RefreshToken;

interface RefreshTokenRepositoryInterface
{
    public function write(RefreshToken $token) : RefreshToken;

    public function find(string $token) : ?RefreshToken;
}
