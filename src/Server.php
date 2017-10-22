<?php

namespace OAuth2\Grant\Implicit;

use OAuth2\Grant\Implicit\Adapter\AdapterInterface;
use OAuth2\Grant\Implicit\Adapter\AuthorizationAdapter;
use OAuth2\Grant\Implicit\Exception\AuthenticationRequiredException;
use OAuth2\Grant\Implicit\Exception\InvalidParameterException;
use OAuth2\Grant\Implicit\Exception\MissingParameterException;
use OAuth2\Grant\Implicit\Factory\AuthorizationAdapterFactory;
use OAuth2\Grant\Implicit\Provider\IdentityProviderInterface;
use OAuth2\Grant\Implicit\Renderer\AuthenticationForm;
use OAuth2\Grant\Implicit\Storage\AccessTokenStorageInterface;
use OAuth2\Grant\Implicit\Storage\ClientStorageInterface;
use OAuth2\Grant\Implicit\Token\AccessTokenFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Stream;

class Server implements ServerInterface
{
    /**
     * @var IdentityProviderInterface
     */
    protected $identityProvider;

    /**
     * @var ClientStorageInterface|null
     */
    protected $clientStorage = null;

    /**
     * @var AccessTokenStorageInterface|null
     */
    protected $tokenStorage = null;

    /**
     * @var AdapterInterface|null
     */
    protected $authorizationAdapter = null;

    /**
     * @var ServerRequestInterface|null
     */
    protected $serverRequest = null;

    /**
     * @var Messages
     */
    protected $messages = null;

    /**
     * @var string
     */
    protected $responseType = 'token';

    /**
     * @var
     */
    protected $authenticationUri = '/login';

    /**
     * Server constructor.
     * @param ServerRequestInterface $request
     */
    public function __construct(
        IdentityProviderInterface $identityProvider,
        ClientStorageInterface $clientStorage,
        AccessTokenStorageInterface $tokenStorage,
        ServerRequestInterface $request = null,
        AdapterInterface $adapter = null
    ) {
        $this->setIdentityProvider($identityProvider);
        $this->setClientStorage($clientStorage);
        $this->setTokenStorage($tokenStorage);

        if ($request !== null) {
            $this->setServerRequest($request);
        }

        if ($adapter !== null) {
            $this->setAuthorizationAdapter($adapter);
        }

        $this->messages = new Messages();
    }

    /**
     * @param ServerRequestInterface|null $request
     * @return ResponseInterface
     */
    public function authorize(ServerRequestInterface $request = null): ResponseInterface
    {
        if ($request !== null) {
            $this->setServerRequest($request);
        }

        if ($this->getIdentityProvider()->hasIdentity() === false) {
            return new Response\RedirectResponse($this->authenticationUri);
        }

        try {
            $accessToken = $this->createAccessToken();
            var_dump($accessToken); die;
        } catch (MissingParameterException|InvalidParameterException $e) {
            return $this->createErrorResponse(400, $e->getMessage());
        }


    }

    /**
     * @return Token\AccessToken
     * @throws \InvalidArgumentException
     */
    protected function createAccessToken()
    {
        $client = $this->getClientFromStorage();
        $identity = $this->getIdentityFromProvider();

        $accessToken = AccessTokenFactory::create(
            $identity,
            $client->getClientId()
        );

        return $accessToken;
    }

    /**
     * @return ClientInterface
     * @throws \InvalidArgumentException
     */
    protected function getClientFromStorage()
    {
        $clientId = $this->getAuthorizationAdapter()->getClientId();
        if ($clientId === null) {
            throw MissingParameterException::create(
                AuthorizationAdapter::CLIENT_ID_KEY
            );
        }

        /** @var ClientInterface $client */
        $client = $this->getClientStorage()->getClientById($clientId);

        return $client;
    }

    /**
     * @return IdentityInterface
     * @throws AuthenticationRequiredException
     */
    protected function getIdentityFromProvider()
    {
        if ($this->getIdentityProvider()->hasIdentity() === false) {
            throw new AuthenticationRequiredException('Authentication failed');
        }

        return $this->getIdentityProvider()->getIdentity();
    }

    /**
     * @return string
     * @throws InvalidParameterException
     */
    protected function getRedirectUri()
    {
        $client = $this->getClientFromStorage();

        $redirectUri = $this->getAuthorizationAdapter()->getRedirectUri();
        if ($redirectUri === null) {
            throw MissingParameterException::create(
                AuthorizationAdapter::REDIRECT_URI_KEY
            );
        }

        foreach ($client->getListRedirectUri() as $uri) {
            if ($uri === $redirectUri) {
                return $redirectUri;
            }
        }

        throw InvalidParameterException::create(
            AuthorizationAdapter::REDIRECT_URI_KEY, $redirectUri
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
     * @return null|AdapterInterface
     */
    public function getAuthorizationAdapter()
    {
        if ($this->authorizationAdapter === null) {
            $this->authorizationAdapter = AuthorizationAdapterFactory::fromServerRequest($this->getServerRequest());
        }

        return $this->authorizationAdapter;
    }

    /**
     * @param null|AdapterInterface $authorizationAdapter
     */
    public function setAuthorizationAdapter(AdapterInterface $authorizationAdapter)
    {
        $this->authorizationAdapter = $authorizationAdapter;
    }

    /**
     * @return ClientStorageInterface|null
     */
    public function getClientStorage()
    {
        return $this->clientStorage;
    }

    /**
     * @param ClientStorageInterface|null $clientStorage
     */
    public function setClientStorage($clientStorage)
    {
        $this->clientStorage = $clientStorage;
    }

    /**
     * @return AccessTokenStorageInterface|null
     */
    public function getTokenStorage()
    {
        return $this->tokenStorage;
    }

    /**
     * @param AccessTokenStorageInterface|null $tokenStorage
     */
    public function setTokenStorage($tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return ServerRequestInterface
     */
    public function getServerRequest()
    {
        if ($this->serverRequest === null) {
            $this->serverRequest = ServerRequestFactory::fromGlobals();
        }

        return $this->serverRequest;
    }

    /**
     * @param null|ServerRequestInterface $serverRequest
     */
    public function setServerRequest(ServerRequestInterface $serverRequest)
    {
        $this->serverRequest = $serverRequest;
    }

    /**
     * @return Messages
     */
    protected function getMessages()
    {
        return $this->messages;
    }
}
