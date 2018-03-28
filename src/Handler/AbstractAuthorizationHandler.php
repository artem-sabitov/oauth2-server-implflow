<?php

namespace OAuth2\Handler;

use OAuth2\ClientInterface;
use OAuth2\IdentityInterface;
use OAuth2\Provider\ClientProviderInterface;
use OAuth2\Provider\IdentityProviderInterface;
use OAuth2\Request\AuthorizationRequest;
use OAuth2\TokenRepositoryInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractAuthorizationHandler
{
    public const AUTHORIZATION_GRANT = '';
    public const REDIRECT_URI_KEY = 'redirect_uri';
    public const RESPONSE_TYPE_KEY = 'response_type';
    public const ACCESS_TOKEN_KEY = 'access_token';
    public const EXPIRES_IN_KEY = 'expires_in';
    public const EXPIRES_ON_KEY = 'expires_on';
    public const GRANT_TYPE_KEY = 'grant_type';
    public const CLIENT_ID_KEY = 'client_id';
    public const CLIENT_SECRET_KEY = 'client_secret';
    public const STATE_KEY = 'state';

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
     * @var TokenRepositoryInterface
     */
    protected $tokenRepository;

    /**
     * AbstractGrantType constructor.
     * @param IdentityProviderInterface $identityProvider
     * @param ClientProviderInterface $clientProvider
     */
    public function __construct(
        array $config,
        ClientProviderInterface $clientProvider,
        TokenRepositoryInterface $tokenRepository
    ) {
        $this->config = $config;
        $this->clientProvider = $clientProvider;
        $this->tokenRepository = $tokenRepository;
    }

    abstract public function canHandle(AuthorizationRequest $request): bool;

    abstract public function handle(IdentityInterface $user, AuthorizationRequest $request): ResponseInterface;

    public function getClientById(string $clientId): ClientInterface
    {
        return $this->clientProvider->getClientById($clientId);
    }
}
