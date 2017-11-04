<?php

namespace OAuth2\Grant\Implicit\Token;

use OAuth2\Grant\Implicit\IdentityInterface;

class AccessTokenFactory
{
    const TOKEN_LENGTH = 128;

    /**
     * @param IdentityInterface $identity
     * @param string $clientId
     * @return AccessToken
     */
    public static function create(IdentityInterface $identity, string $clientId)
    {
        $accessToken = (new AccessToken())
            ->setIdentity($identity)
            ->setClientId($clientId)
            ->setAccessToken(self::generateRandomString(static::TOKEN_LENGTH));

        return $accessToken;
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
