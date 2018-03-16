<?php

namespace OAuth2\Handler;

use OAuth2\ClientInterface;
use OAuth2\IdentityInterface;
use OAuth2\Options\Options;
use OAuth2\Provider\ClientProviderInterface;
use OAuth2\Provider\IdentityProviderInterface;
use OAuth2\Request\AuthorizationRequest;
use OAuth2\Storage\AccessTokenStorageInterface;
use OAuth2\Token\AccessToken;
use Psr\Http\Message\UriInterface;

abstract class AbstractAuthorizationHandler
{
    /**
     * @var Options
     */
    protected $options;

    /**
     * @var IdentityProviderInterface
     */
    protected $identityProvider;

    /**
     * @var ClientProviderInterface
     */
    protected $clientProvider;

    /**
     * @var AccessTokenStorageInterface
     */
    protected $accessTokenStorage;

    /**
     * @var array
     */
    protected $responseData;

    /**
     * AbstractGrantType constructor.
     * @param IdentityProviderInterface $identityProvider
     * @param ClientProviderInterface $clientProvider
     */
    public function __construct(
        Options $options,
        IdentityProviderInterface $identityProvider,
        ClientProviderInterface $clientProvider,
        AccessTokenStorageInterface $accessTokenStorage
    ) {
        $this->options = $options;
        $this->identityProvider = $identityProvider;
        $this->clientProvider = $clientProvider;
        $this->accessTokenStorage = $accessTokenStorage;
    }

    abstract public function handle(AuthorizationRequest $request): AbstractAuthorizationHandler;

    abstract protected function generateAccessToken(): AccessToken;

    abstract protected function generateRedirectUri(): UriInterface;

    public function getResponseData(): array
    {
        return $this->responseData;
    }

    public function getIdentityProvider(): IdentityProviderInterface
    {
        return $this->identityProvider;
    }

    public function getClientProvider(): ClientProviderInterface
    {
        return $this->clientProvider;
    }

    public function getIdentity(): IdentityInterface
    {
        return $this->getIdentityProvider()->getIdentity();
    }

    public function getClientById(string $clientId): ClientInterface
    {
        return $this->getClientProvider()->getClientById($clientId);
    }
}
