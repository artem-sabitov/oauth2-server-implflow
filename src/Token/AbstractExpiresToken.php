<?php

namespace OAuth2\Token;

use OAuth2\ClientInterface;
use OAuth2\IdentityInterface;

abstract class AbstractExpiresToken
{
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
        IdentityInterface $identity,
        ClientInterface $client,
        int $expires
    ) {
        $this->identity = $identity;
        $this->client = $client;
        $this->expires = $expires;
    }

    abstract public function getValue(): string;

    public function getIdentity(): IdentityInterface
    {
        return $this->identity;
    }

    public function getClient(): ClientInterface
    {
        return $this->client;
    }

    public function getExpires(): int
    {
        return $this->expires;
    }
}
