<?php

namespace OAuth2\Token;

use OAuth2\ClientInterface;
use OAuth2\IdentityInterface;

class AuthorizationCode extends AbstractExpiresToken
{
    /**
     * @var string
     */
    protected $authorizationCode;

    /**
     * @var bool
     */
    protected $used;

    /**
     * AuthorizationCode constructor.
     * @param string $authorizationCode
     * @param IdentityInterface $identity
     * @param ClientInterface $client
     * @param int $expires
     */
    public function __construct(
        string $authorizationCode,
        IdentityInterface $identity,
        ClientInterface $client,
        int $expires
    ) {
        $this->authorizationCode = $authorizationCode;
        $this->used = false;
        parent::__construct($identity, $client, $expires);
    }

    public function getValue(): string
    {
        return $this->authorizationCode;
    }

    public function isUsed(): bool
    {
        return $this->used;
    }

    public function setUsed(bool $used) : AuthorizationCode
    {
        $this->used = $used;

        return $this;
    }

}
