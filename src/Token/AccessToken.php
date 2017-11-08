<?php

namespace OAuth2\Grant\Implicit\Token;

use OAuth2\Grant\Implicit\ClientInterface;
use OAuth2\Grant\Implicit\IdentityInterface;

class AccessToken
{
    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var IdentityInterface
     */
    protected $identity;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var int
     */
    protected $expires;

    /**
     * AccessToken constructor.
     * @param IdentityInterface $identity
     * @param ClientInterface $client
     */
    public function __construct(
        string $accessToken,
        IdentityInterface $identity,
        ClientInterface $client,
        int $expires
    ) {
        $this->accessToken = $accessToken;
        $this->identity = $identity;
        $this->client = $client;
        $this->expires = $expires;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @return mixed
     */
    public function getIdentity(): IdentityInterface
    {
        return $this->identity;
    }

    /**
     * @return string
     */
    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    /**
     * @return int
     */
    public function getExpires(): int
    {
        return $this->expires;
    }
}
