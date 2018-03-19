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
        parent::__construct($identity, $client, $expires);
    }

    public function getValue(): string
    {
        return $this->authorizationCode;
    }
}
