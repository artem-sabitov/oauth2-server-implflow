<?php

namespace OAuth2\Grant\Implicit\Token;

use DateTime;
use OAuth2\Grant\Implicit\ClientInterface;
use OAuth2\Grant\Implicit\IdentityInterface;

class AccessTokenFactory
{
    const TOKEN_LENGTH = 128;
    const EXPIRATION_TIME = 60 * 60; // seconds

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
            $client,
            self::generateExpiresAt()
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

    private static function generateExpiresAt()
    {
        return (new DateTime())->getTimestamp() + self::EXPIRATION_TIME ;
    }
}
