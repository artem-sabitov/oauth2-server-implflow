<?php

namespace OAuth2\Token;

use OAuth2\ClientInterface;
use OAuth2\IdentityInterface;

class AccessToken extends AbstractExpiresToken
{
    /**
     * @var string
     */
    protected $accessToken;

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
        parent::__construct($identity, $client, $expires);
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->accessToken;
    }
}
