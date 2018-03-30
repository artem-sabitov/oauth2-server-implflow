<?php

namespace OAuth2\Token;

use DateTime;
use OAuth2\ClientInterface;
use OAuth2\Exception\RuntimeException;
use OAuth2\IdentityInterface;
use Zend\Json\Json;

class TokenBuilder
{
    const STATE_LENGTH = 12;

    /**
     * @var callable
     */
    protected $className;

    /**
     * @var IdentityInterface
     */
    private $identity;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var string
     */
    private $issuerIdentifier = '';

    /**
     * @var int
     */
    private $expirationTime = 60 * 60; // seconds

    /**
     * @var string
     */
    private $state;

    /**
     * TokenBuilder constructor.
     */
    public function __construct()
    {
        $this->state = self::generateState();
    }

    public function setTokenClass(string $className) : TokenBuilder
    {
        if (! class_exists($className)) {
            throw new RuntimeException(sprintf(
                'Can not use token class \'%s\'',
                $className
            ));
        }

        $this->className = $className;

        return $this;
    }

    public function setIdentity(IdentityInterface $identity) : TokenBuilder
    {
        $this->identity = $identity;

        return $this;
    }

    public function setClient(ClientInterface $client) : TokenBuilder
    {
        $this->client = $client;

        return $this;
    }

    public function setExpirationTime(int $seconds) : TokenBuilder
    {
        $this->expirationTime = $seconds;

        return $this;
    }

    public function setIssuerIdentifier(string $identifier) : TokenBuilder
    {
        $this->issuerIdentifier = $identifier;

        return $this;
    }

    /**
     * @return TokenInterface
     */
    public function generate(): AbstractExpiresToken
    {
        $expires = $this->generateExpiresAt();
        $token = $this->generateAccessTokenString($expires);

        return new $this->className(
            $token,
            $this->identity,
            $this->client,
            $expires
        );
    }

    /**
     * @param IdentityInterface $identity
     * @param ClientInterface $client
     * @return string
     */
    private function generateAccessTokenString(string $expires) : string
    {
        $payload = [
            'iss' => $this->issuerIdentifier,
            'sub' => $this->identity->getIdentityId(),
            'aud' => $this->client->getClientId(),
            'exp' => $expires,
            'auth_time' => (new DateTime())->getTimestamp(),
            'nonce' => self::generateState(),
        ];

        return self::base64UrlEncode(Json::encode($payload));
    }

    /**
     * @return string
     */
    private function generateExpiresAt(): string
    {
        return (new DateTime())->getTimestamp() + $this->expirationTime;
    }

    /**
     * @return string
     */
    protected static function generateState(): string
    {
        $bytes = random_bytes(self::STATE_LENGTH);

        return bin2hex($bytes);
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
}
