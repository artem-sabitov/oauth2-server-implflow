<?php

namespace OAuth2\Grant\Implicit;

use OAuth2\Grant\Implicit\Adapter\AdapterInterface;
use OAuth2\Grant\Implicit\Exception\AuthenticationException;
use OAuth2\Grant\Implicit\Exception\AuthenticationRequiredException;
use OAuth2\Grant\Implicit\Exception\ParameterException;
use OAuth2\Grant\Implicit\Factory\AuthorizationRequestFactory;
use OAuth2\Grant\Implicit\Options\ServerOptions;
use OAuth2\Grant\Implicit\Provider\ClientProviderInterface;
use OAuth2\Grant\Implicit\Provider\IdentityProviderInterface;
use OAuth2\Grant\Implicit\Storage\TokenStorageInterface;
use OAuth2\Grant\Implicit\Token\AccessTokenFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Uri;

class Server implements ServerInterface
{
    /**
     * ServerOptions
     */
    protected $options;

    /**
     * @var AuthorizationRequest
     */
    protected $authorizationRequest;

    /**
     * @var IdentityProviderInterface
     */
    protected $identityProvider;

    /**
     * @var ClientProviderInterface
     */
    protected $clientProvider;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var Messages
     */
    protected $messages;

    /**
     * Server constructor.
     * @param ServerRequestInterface $request
     */
    public function __construct(
        ServerOptions $serverOptions,
        IdentityProviderInterface $identityProvider,
        ClientProviderInterface $clientProvider,
        TokenStorageInterface $tokenStorage
    ) {
        $this->options = $serverOptions;
        $this->identityProvider = $identityProvider;
        $this->clientProvider = $clientProvider;
        $this->tokenStorage = $tokenStorage;

        $this->messages = new Messages();
    }

    /**
     * @param ServerRequestInterface|null $request
     * @return ResponseInterface
     */
    public function authorize(ServerRequestInterface $request = null): ResponseInterface
    {
        if ($this->isAuthenticated() === false) {
            return new Response\RedirectResponse(
                $this->options->getAuthenticationUri()
            );
        }

        try {
            if ($request !== null) {
                $this->authorizationRequest =
                    AuthorizationRequestFactory::fromServerRequest($request);
            }

            $redirectUri = $this->getRedirectUri();
            $accessToken = $this->createAccessToken();

            $query = [
                'access_token' => $accessToken->getAccessToken(),
            ];
        } catch (ParameterException $e) {
            return $this->createErrorResponse(400, $e->getMessage());
        }

        $redirectUri = $this->createSuccessRedirectUri($redirectUri, $query);

        return new Response\RedirectResponse($redirectUri);
    }

    /**
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return $this->getIdentityProvider()->hasIdentity();
    }

    /**
     * @return Token\AccessToken
     * @throws \InvalidArgumentException
     */
    protected function createAccessToken()
    {
        $client = $this->getClientFromProvider();
        $identity = $this->getIdentityFromProvider();

        $accessToken = AccessTokenFactory::create(
            $identity,
            $client->getClientId()
        );

        return $accessToken;
    }

    /**
     * @param array $query
     * @return UriInterface
     */
    protected function createSuccessRedirectUri(string $uri, array $query): UriInterface
    {
        $uri = new Uri($uri);
        $uri = $uri->withQuery(http_build_query($query));

        return $uri;
    }

    /**
     * @return ClientInterface
     * @throws ParameterException
     */
    protected function getClientFromProvider()
    {
        $clientId = $this->getAuthorizationRequest()->getClientId();

        /** @var ClientInterface $client */
        $client = $this->getClientProvider()->getClientById($clientId);

        return $client;
    }

    /**
     * @return IdentityInterface
     * @throws AuthenticationException
     */
    protected function getIdentityFromProvider()
    {
        if ($this->getIdentityProvider()->hasIdentity() === false) {
            throw new AuthenticationException('Authentication failed');
        }

        return $this->getIdentityProvider()->getIdentity();
    }

    /**
     * @return string
     * @throws ParameterException
     */
    protected function getRedirectUri()
    {
        $client = $this->getClientFromProvider();
        $redirectUri = $this->getAuthorizationRequest()->getRedirectUri();

        foreach ($client->getListRedirectUri() as $uri) {
            if ($uri === $redirectUri) {
                return $redirectUri;
            }
        }

        throw ParameterException::createInvalidParameter(
            AuthorizationRequest::REDIRECT_URI_KEY,
            $redirectUri
        );
    }

    /**
     * @return ErrorResponse
     */
    protected function createErrorResponse(int $code, string $message): ResponseInterface
    {
        $body = [
            'code' => $code,
            'message' => $message,
        ];

        $response = new Response\JsonResponse($body);
        $response
            ->withHeader('ContentType', 'application/json')
            ->withStatus($code);

        return $response;
    }

    /**
     * @return IdentityProviderInterface
     */
    public function getIdentityProvider(): IdentityProviderInterface
    {
        return $this->identityProvider;
    }

    /**
     * @param IdentityProviderInterface $identityProvider
     */
    public function setIdentityProvider(IdentityProviderInterface $identityProvider)
    {
        $this->identityProvider = $identityProvider;
    }

    /**
     * @param null|AdapterInterface $authorizationAdapter
     */
    public function setAuthorizationAdapter(AdapterInterface $authorizationAdapter)
    {
        $this->authorizationAdapter = $authorizationAdapter;
    }

    /**
     * @return ClientProviderInterface
     */
    public function getClientProvider()
    {
        return $this->clientProvider;
    }

    /**
     * Method returning a new immutable object with new value.
     * @param ClientProviderInterface $clientProvider
     */
    public function setClientProvider($clientProvider)
    {
        $server = clone $this;
        $server->clientProvider = $clientProvider;

        return $server;
    }

    /**
     * @return TokenStorageInterface
     */
    public function getTokenStorage()
    {
        return $this->tokenStorage;
    }

    /**
     * Method returning a new immutable object with new value.
     * @param TokenStorageInterface $tokenStorage
     */
    public function setTokenStorage($tokenStorage)
    {
        $server = clone $this;
        $server->tokenStorage = $tokenStorage;

        return $server;
    }

    /**
     * @return AuthorizationRequest
     */
    public function getAuthorizationRequest()
    {
        if ($this->authorizationRequest === null) {
            $this->authorizationRequest =
                AuthorizationRequestFactory::fromGlobalServerRequest();
        }

        return $this->authorizationRequest;
    }

    /**
     * Method returning a new immutable object with new value.
     * @param AuthorizationRequest $request
     * @return Server
     */
    public function setAuthorizationRequest(AuthorizationRequest $request)
    {
        $server = clone $this;
        $server->authorizationRequest = $request;

        return $server;
    }

    /**
     * @return Messages
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
