<?php

namespace OAuth2\Grant\Implicit\Token;

use DateTime;
use OAuth2\Grant\Implicit\ClientInterface;
use OAuth2\Grant\Implicit\IdentityInterface;
use Zend\Crypt\Password\Bcrypt;
use Zend\Json\Json;

class AccessTokenBuilder
{
    const STATE_LENGTH = 12;
    const EXPIRATION_TIME = 60 * 60; // seconds

    /**
     * @param IdentityInterface $identity
     * @param string $clientId
     * @return AccessToken
     */
    public static function create(IdentityInterface $identity, ClientInterface $client): AccessToken
    {
        $instance = new self();

        return new AccessToken(
            $instance->generate($identity, $client),
            $identity,
            $client,
            $instance->generateExpiresAt()
        );
    }

    /**
     * @param IdentityInterface $identity
     * @param ClientInterface $client
     * @return string
     */
    protected function generate(IdentityInterface $identity, ClientInterface $client): string
    {
        $payload = [
            'id' => $identity->getIdentityId(),
            'client_id' => $client->getClientId(),
            'expires' => $this->generateExpiresAt(),
            'state' => $this->generateState(),
        ];

        return $this->base64UrlEncode(Json::encode($payload));
    }

    /**
     * @param $value
     * @return bool|string
     */
    protected function base64UrlEncode($value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    /**
     * @param $value
     * @return bool|string
     */
    protected function base64UrlDecode($value): string
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $value));
    }

    /**
     * @return string
     */
    protected function generateExpiresAt(): string
    {
        return (new DateTime())->getTimestamp() + self::EXPIRATION_TIME ;
    }

    /**
     * @return string
     */
    protected function generateState(): string
    {
        $bytes = random_bytes(self::STATE_LENGTH);

        return bin2hex($bytes);
    }
}
