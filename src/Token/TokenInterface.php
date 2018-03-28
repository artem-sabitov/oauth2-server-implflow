<?php

declare(strict_types=1);

namespace OAuth2\Token;

interface TokenInterface
{
    public function getValue(): string;

    public function getExpires(): int;
}
