<?php

namespace OAuth2\Handler;

use OAuth2\IdentityInterface;
use OAuth2\Provider\IdentityProviderInterface;
use OAuth2\Repository\ClientRepositoryInterface;
use OAuth2\Repository\AccessTokenRepositoryInterface;
use OAuth2\Request\AuthorizationRequest;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractAuthorizationHandler
{
    public const AUTHORIZATION_GRANT = '';
    public const REDIRECT_URI_KEY = 'redirect_uri';
    public const RESPONSE_TYPE_KEY = 'response_type';
    public const CLIENT_ID_KEY = 'client_id';
    public const CLIENT_SECRET_KEY = 'client_secret';
    public const STATE_KEY = 'state';
    public const ACCESS_TOKEN_KEY = 'access_token';
    public const REFRESH_TOKEN_KEY = 'refresh_token';
    public const EXPIRES_IN_KEY = 'expires_in';
    public const EXPIRES_ON_KEY = 'expires_on';

    /**
     * @var array
     */
    protected $config;

    /**
     * @var IdentityProviderInterface
     */
    protected $identityProvider;

    /**
     * @var ClientRepositoryInterface
     */
    protected $clientRepository;

    /**
     * @var AccessTokenRepositoryInterface
     */
    protected $accessTokenRepository;

    /**
     * AbstractGrantType constructor.
     * @param IdentityProviderInterface $identityProvider
     * @param ClientRepositoryInterface $clientProvider
     */
    public function __construct(
        array $config,
        ClientRepositoryInterface $clientRepository,
        AccessTokenRepositoryInterface $accessTokenRepository
    ) {
        $this->config = $config;
        $this->clientRepository = $clientRepository;
        $this->accessTokenRepository = $accessTokenRepository;
    }

    abstract public function canHandle(AuthorizationRequest $request): bool;

    abstract public function handle(IdentityInterface $user, AuthorizationRequest $request): ResponseInterface;
}
