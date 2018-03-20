<?php

namespace OAuth2\Token;

use OAuth2\ClientInterface;
use OAuth2\IdentityInterface;

class RefreshToken extends AbstractExpiresToken
{
    /**
     * @var string
     */
    protected $refreshToken;

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
        string $refreshToken,
        AccessToken $accessToken,
        int $expires
    ) {
        $this->refreshToken = $refreshToken;
        $this->accessToken = $accessToken;
        parent::__construct(
            $accessToken->getIdentity(),
            $accessToken->getClient(),
            $expires
        );
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->refreshToken;
    }
}
