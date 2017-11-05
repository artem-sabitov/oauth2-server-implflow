<?php

namespace OAuth2\Grant\Implicit\Options;

use Zend\Stdlib\AbstractOptions;

class ServerOptions extends AbstractOptions
{
    /**
     * @var string
     */
    protected $authenticationUri = '/login';

    /**
     * @var string
     */
    protected $availableResponseType = 'token';

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
    public function getAvailableResponseType(): string
    {
        return $this->availableResponseType;
    }

    /**
     * @param string $availableResponseType
     */
    public function setAvailableResponseType(string $availableResponseType): ServerOptions
    {
        $this->availableResponseType = $availableResponseType;

        return $this;
    }
}
