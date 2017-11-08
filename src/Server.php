<?php

namespace OAuth2\Grant\Implicit;

use OAuth2\Grant\Implicit\Exception\ParameterException;
use OAuth2\Grant\Implicit\Factory\AuthorizationRequestFactory;
use OAuth2\Grant\Implicit\Options\ServerOptions;
use OAuth2\Grant\Implicit\Provider\ClientProviderInterface;
use OAuth2\Grant\Implicit\Provider\IdentityProviderInterface;
use OAuth2\Grant\Implicit\Storage\TokenStorageInterface;
use OAuth2\Grant\Implicit\Token\AccessToken;
use OAuth2\Grant\Implicit\Token\AccessTokenFactory;
use OAuth2\Grant\Implicit\Validator\AuthorizationRequestValidator;
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
    }

    /**
     * @param ServerRequestInterface|null $request
     * @return ResponseInterface
     */
    public function authorize(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->isAuthenticated() === false) {
            return new Response\RedirectResponse(
                $this->options->getAuthenticationUri()
            );
        }

        try {
            $request = AuthorizationRequestFactory::fromServerRequest($request);
            $this->validateAuthorizationRequest($request);
            $this->authorizationRequest = $request;

            $token = $this->createToken();
            $this->getTokenStorage()->write($token);
            $redirectUri = $this->createRedirectUriWithAccessToken($token);
        } catch (ParameterException $e) {
            return $this->createErrorResponse(400, $e->getMessages());
        }

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
     * @param AuthorizationRequest $request
     * @throw ParameterException
     */
    public function validateAuthorizationRequest(AuthorizationRequest $request): void
    {
        $validator = $this->getAuthorizationRequestValidator();

        if ($validator->validate($request) === false) {
            $messages = $validator->getMessages();
            throw ParameterException::create($messages);
        }
    }

    /**
     * @return Token\AccessToken
     * @throws \InvalidArgumentException
     */
    protected function createToken()
    {
        $client = $this
            ->getClientProvider()
            ->getClientById(
                $this->getAuthorizationRequest()->getClientId()
            );

        $identity = $this
            ->getIdentityProvider()
            ->getIdentity();

        return AccessTokenFactory::create($identity, $client);
    }

    /**
     * @param array $query
     * @return UriInterface
     */
    public function createRedirectUriWithAccessToken(AccessToken $token): UriInterface
    {
        $redirectUri = $this->getAuthorizationRequest()->getRedirectUri();
        $query = http_build_query([
            $this->options->getAccessTokenQueryKey() => $token->getAccessToken()
        ]);

        return (new Uri($redirectUri))->withQuery($query);
    }

    /**
     * @return Response\JsonResponse
     */
    public function createErrorResponse(int $code, array $message): ResponseInterface
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
     * Method returning a new immutable object with new value.
     * @param IdentityProviderInterface $identityProvider
     */
    public function setIdentityProvider(IdentityProviderInterface $identityProvider)
    {
        $server = clone $this;
        $server->identityProvider = $identityProvider;

        return $server;
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
     * @return AuthorizationRequestValidator
     */
    public function getAuthorizationRequestValidator()
    {
        $responseType = $this->options->getSupportedResponseType();
        $clientId = $this->getAuthorizationRequest()->getClientId();
        $clientRedirectUri = $this
            ->getClientProvider()
            ->getClientById($clientId)
            ->getRedirectUri();

        return new AuthorizationRequestValidator(
            $this->getClientProvider(),
            $responseType,
            $clientRedirectUri
        );
    }
}
