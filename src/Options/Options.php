<?php

namespace OAuth2\Options;

use OAuth2\GrantType\AbstractGrantType;
use Zend\Stdlib\AbstractOptions;
use Zend\Stdlib\ArrayUtils;

class Options extends AbstractOptions
{
    const DEFAULT_ACCESS_TOKEN_KEY = 'access_token';
    const DEFAULT_REDIRECT_URI_KEY = 'redirect_uri';

    /**
     * @var string
     */
    protected $authenticationUri = '';

    /**
     * @var array
     */
    protected $supportedResponseTypes = [];

    /**
     * @var string
     */
    protected $accessTokenQueryKey = self::DEFAULT_ACCESS_TOKEN_KEY;

    /**
     * @var string
     */
    protected $redirectUriQueryKey = self::DEFAULT_REDIRECT_URI_KEY;

    public function getAuthenticationUri(): string
    {
        return $this->authenticationUri;
    }

    public function setAuthenticationUri(string $authenticationUri): void
    {
        $this->authenticationUri = $authenticationUri;
    }

    public function getSupportedResponseTypes(): array
    {
        return $this->supportedResponseType;
    }

    public function setSupportedResponseTypes(array $responseTypes): void
    {
        $this->supportedResponseType = $responseTypes;
    }

    public function addSupportedResponseType(AbstractGrantType $type): void
    {
        $className = get_class($type);
        if (! ArrayUtils::inArray($className, $this->supportedResponseTypes)) {
            $this->supportedResponseTypes[$type->getTypeAsString()] = $className;
        }
    }

    public function getAccessTokenQueryKey(): string
    {
        return $this->accessTokenQueryKey;
    }

    public function setAccessTokenQueryKey(string $accessTokenQueryKey): void
    {
        $this->accessTokenQueryKey = $accessTokenQueryKey;
    }

    public function getRedirectUriQueryKey(): string
    {
        return $this->redirectUriQueryKey;
    }

    public function setRedirectUriQueryKey(string $redirectUriQueryKey): void
    {
        $this->redirectUriQueryKey = $redirectUriQueryKey;
    }
}
