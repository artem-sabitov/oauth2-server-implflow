<?php

namespace OAuth2\Grant\Implicit;

use OAuth2\Grant\Implicit\Adapter\AdapterInterface;
use OAuth2\Grant\Implicit\Adapter\AuthorizationAdapter;
use OAuth2\Grant\Implicit\Factory\AuthorizationAdapterFactory;
use OAuth2\Grant\Implicit\Provider\IdentityProviderInterface;
use OAuth2\Grant\Implicit\Storage\AccessTokenStorageInterface;
use OAuth2\Grant\Implicit\Storage\ClientStorageInterface;
use OAuth2\Grant\Implicit\Token\AccessTokenFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequestFactory;

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
     * @var array
     */
    protected $result = [];

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

        $adapter = $this->getAuthorizationAdapter();

        if ($adapter->getResponseType() !== $this->responseType) {
            $this->getMessages()->addErrorMessage(
                ErrorResponse::UNSUPPORTED_RESPONSE_TYPE, sprintf(
                'OAuth2 parameter `%s`: `%s` not available to the Implicit Grant.',
                AuthorizationAdapter::RESPONSE_TYPE_KEY, $adapter->getResponseType()
            ));
        }

        /** @var ClientInterface $client */
        $client = $this
            ->getClientStorage()
            ->getClientById($adapter->getClientId());

        var_dump($this->getMessages()->toArray());
        var_dump(123); die;

        $isUriCorrect = false;
        $redirectUri = $adapter->getRedirectUri();
        foreach ($client->getListAvailableRedirectUri() as $uri) {
            if ($redirectUri === $uri) {
                $isUriCorrect = true;
            }
        }

        if ($isUriCorrect === false) {
            $this->getMessages()->addErrorMessage(sprintf(
                'The parameter %s `%s` not available to the %s: `%s`',
                AuthorizationAdapter::REDIRECT_URI_KEY, $redirectUri,
                AuthorizationAdapter::CLIENT_ID_KEY, $client->getClientId()
            ));
        }

        if ($this->getIdentityProvider()->hasIdentity() === false) {
            $this->getMessages()->addErrorMessage(sprintf(
                'The identity was not provided.'
            ));
        }

        $accessToken = AccessTokenFactory::create(
            $this->getIdentityProvider()->getIdentity(),
            $client->getClientId()
        );
        $this->getTokenStorage()->write($accessToken);

        return $this->createAuthorizationResult();
    }

    /**
     * @return Result
     */
    public function createAuthorizationResult(): Result
    {
        $result = new Result();

        return $result;
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
