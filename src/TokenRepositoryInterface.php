<?php

namespace OAuth2;

use OAuth2\Token\AuthorizationCode;
use OAuth2\Token\TokenInterface;

interface TokenRepositoryInterface
{
    public function write(TokenInterface $token) : void;

    public function authorizeByCode(string $code) : AuthorizationCode;
}
