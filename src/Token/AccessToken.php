<?php

namespace OAuth2\Grant\Implicit\Token;

use OAuth2\Grant\Implicit\IdentityInterface;

class AccessToken
{
    /**
     * @var IdentityInterface
     */
    protected $identity;

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var int
     */
    protected $expires;

    /**
     * @return mixed
     */
    public function getIdentity(): IdentityInterface
    {
        return $this->identity;
    }

    /**
     * @param mixed $identity
     * @return AccessToken
     */
    public function setIdentity(IdentityInterface $identity)
    {
        $this->identity = $identity;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     * @return AccessToken
     */
    public function setAccessToken(string $accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     * @return AccessToken
     */
    public function setClientId(string $clientId)
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * @return int
     */
    public function getExpires(): int
    {
        return $this->expires;
    }

    /**
     * @param int $expires
     * @return AccessToken
     */
    public function setExpires(int $expires)
    {
        $this->expires = $expires;

        return $this;
    }
}
