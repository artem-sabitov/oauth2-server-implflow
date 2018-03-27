<?php

namespace OAuth2\Handler;

use OAuth2\ClientInterface;
use OAuth2\IdentityInterface;
use OAuth2\Provider\ClientProviderInterface;
use OAuth2\Provider\IdentityProviderInterface;
use OAuth2\Request\AuthorizationRequest;
use OAuth2\Storage\AccessTokenStorageInterface;
use OAuth2\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

abstract class AbstractAuthorizationHandler
{
    public const AUTHORIZATION_GRANT = '';
    public const REDIRECT_URI_KEY = 'redirect_uri';
    public const RESPONSE_TYPE_KEY = 'response_type';
    public const ACCESS_TOKEN_KEY = 'access_token';

    protected const HEADER_LOCATION = 'Location';

    /**
     * @var array
     */
    protected $config;

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
    protected $responseData = [];

    /**
     * AbstractGrantType constructor.
     * @param IdentityProviderInterface $identityProvider
     * @param ClientProviderInterface $clientProvider
     */
    public function __construct(
        array $config,
        ClientProviderInterface $clientProvider,
        AccessTokenStorageInterface $accessTokenStorage
    ) {
        $this->config = $config;
        $this->clientProvider = $clientProvider;
        $this->accessTokenStorage = $accessTokenStorage;
    }

    abstract public function canHandle(AuthorizationRequest $request): bool;

    abstract public function handle(IdentityInterface $user, AuthorizationRequest $request): ResponseInterface;

    abstract protected function generateAccessToken(): AccessToken;

    abstract protected function generateRedirectUri(): UriInterface;

    public function getClientById(string $clientId): ClientInterface
    {
        return $this->clientProvider->getClientById($clientId);
    }
}
