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
     * @var AccessToken
     */
    protected $accessToken;

    /**
     * RefreshToken constructor.
     * @param string $refreshToken
     * @param AccessToken $accessToken
     * @param int $expires
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

    public function getValue(): string
    {
        return $this->refreshToken;
    }

    public function getAccessToken(): AccessToken
    {
        return $this->accessToken;
    }
}
