<?php

declare(strict_types=1);

namespace OAuth2\Repository;

use OAuth2\Token\AuthorizationCode;

interface AuthorizationCodeRepositoryInterface
{
    public function write(AuthorizationCode $code) : AuthorizationCode;

    public function find(string $code) : ?AuthorizationCode;
}
