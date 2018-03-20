<?php

namespace OAuth2\Token;

use DateTime;
use OAuth2\ClientInterface;
use OAuth2\Exception\RuntimeException;
use OAuth2\IdentityInterface;
use Zend\Json\Json;

abstract class TokenGenerator
{
    const STATE_LENGTH = 12;
    const EXPIRATION_TIME = 60 * 60; // seconds

    /**
     * @param IdentityInterface $identity
     * @param string $clientId
     * @return AccessToken
     */
    public static function generate(
        string $tokenClassName,
        IdentityInterface $identity,
        ClientInterface $client
    ): AbstractExpiresToken {
        if (! class_exists($tokenClassName)) {
            throw new RuntimeException("Can not generate token of type {$tokenClassName}");
        }
        return new $tokenClassName(
            self::generateAccessTokenString($identity, $client),
            $identity,
            $client,
            self::generateExpiresAt()
        );
    }

    /**
     * @param IdentityInterface $identity
     * @param ClientInterface $client
     * @return string
     */
    public static function generateAccessTokenString(IdentityInterface $identity, ClientInterface $client): string
    {
        $payload = [
            'id' => $identity->getIdentityId(),
            'client_id' => $client->getClientId(),
            'expires' => self::generateExpiresAt(),
            'state' => self::generateState(),
        ];

        return self::base64UrlEncode(Json::encode($payload));
    }

    /**
     * @return string
     */
    public static function generateExpiresAt(): string
    {
        return (new DateTime())->getTimestamp() + self::EXPIRATION_TIME ;
    }

    /**
     * @param $value
     * @return bool|string
     */
    protected static function base64UrlEncode($value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    /**
     * @param $value
     * @return bool|string
     */
    protected static function base64UrlDecode($value): string
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $value));
    }

    /**
     * @return string
     */
    protected static function generateState(): string
    {
        $bytes = random_bytes(self::STATE_LENGTH);

        return bin2hex($bytes);
    }
}
