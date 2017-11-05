<?php

namespace OAuth2\Grant\Implicit\Options;

use Zend\Stdlib\AbstractOptions;

class ServerOptions extends AbstractOptions
{
    const DEFAULT_RESPONSE_TYPE = 'token';

    /**
     * @var string
     */
    protected $authenticationUri = '';

    /**
     * @var string
     */
    protected $supportedResponseType = self::DEFAULT_RESPONSE_TYPE;

    /**
     * @return string
     */
    public function getAuthenticationUri(): string
    {
        return $this->authenticationUri;
    }

    /**
     * @param string $authenticationUri
     * @return ServerOptions
     */
    public function setAuthenticationUri(string $authenticationUri): ServerOptions
    {
        $this->authenticationUri = $authenticationUri;

        return $this;
    }

    /**
     * @return string
     */
    public function getSupportedResponseType(): string
    {
        return $this->supportedResponseType;
    }

    /**
     * @param string $availableResponseType
     */
    public function setSupportedResponseType(string $responseType): ServerOptions
    {
        $new = clone $this;
        $new->supportedResponseType = $responseType;

        return $new;
    }
}
