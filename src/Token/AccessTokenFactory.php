<?php

namespace OAuth2\Grant\Implicit\Token;

use OAuth2\Grant\Implicit\ClientInterface;
use OAuth2\Grant\Implicit\IdentityInterface;

class AccessTokenFactory
{
    const TOKEN_LENGTH = 128;

    /**
     * @param IdentityInterface $identity
     * @param string $clientId
     * @return AccessToken
     */
    public static function create(IdentityInterface $identity, ClientInterface $client)
    {
        return new AccessToken(
            self::generateRandomString(static::TOKEN_LENGTH),
            $identity,
            $client
        );
    }

    /**
     * @param int $length
     * @return string
     */
    private static function generateRandomString($length = 64)
    {
        $bytes = random_bytes($length);
        $string = bin2hex($bytes);

        return $string;
    }
}
