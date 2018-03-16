<?php

namespace OAuth2;

use OAuth2\Exception\ParameterException;
use OAuth2\Factory\AuthorizationRequestFactory;
use OAuth2\Handler\AbstractAuthorizationHandler;
use OAuth2\Options\Options;
use OAuth2\Provider\ClientProviderInterface;
use OAuth2\Provider\IdentityProviderInterface;
use OAuth2\Validator\AuthorizationRequestValidator;
use OAuth2\Request\AuthorizationRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

class Server implements ServerInterface
{
    /**
     * Options
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
     * @var AbstractAuthorizationHandler
     */
    protected $authorizationHandler;

    /**
     * Server constructor.
     * @param ServerRequestInterface $request
     */
    public function __construct(
        Options $serverOptions,
        IdentityProviderInterface $identityProvider,
        ClientProviderInterface $clientProvider,
        AbstractAuthorizationHandler $authorizationHandler
    ) {
        $this->options = $serverOptions;
        $this->identityProvider = $identityProvider;
        $this->clientProvider = $clientProvider;
        $this->authorizationHandler = $authorizationHandler;
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

        $this->authorizationRequest = $this->createAuthorizationRequest($request);

        try {
            $this->validateAuthorizationRequest($this->authorizationRequest);
        } catch (ParameterException $e) {
            return $this->createErrorResponse(400, $e->getMessages());
        }

        $handler = $this->handleAuthorizationRequest($this->authorizationRequest);

        return $this->createResponse($handler->getResponseData());
    }

    /**
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return $this->getIdentityProvider()->hasIdentity();
    }

    public function createAuthorizationRequest(ServerRequestInterface $request): AuthorizationRequest
    {
        return AuthorizationRequestFactory::fromServerRequest($request);
    }

    /**
     * @throw ParameterException
     */
    public function validateAuthorizationRequest(AuthorizationRequest $request): void
    {
        $validator = $this->getAuthorizationRequestValidator();

        if ($validator->validate($request) === false) {
            $messages = $validator->getErrorMessages();
            throw ParameterException::create($messages);
        }
    }

    /**
     * @throws ParameterException
     */
    public function handleAuthorizationRequest(AuthorizationRequest $request): AbstractAuthorizationHandler
    {
        return $this->authorizationHandler->handle($request);
    }

    public function createResponse(array $data): ResponseInterface
    {
        if (isset($data['headers']) === true) {
            $headers = $data['headers'];
            if ($headers['Location']) {
                return new Response\RedirectResponse($headers['Location']);
            }
        }

        var_dump($data); die;
    }

    /**
     * @return Response\JsonResponse
     */
    public function createErrorResponse(int $code, array $message): ResponseInterface
    {
        $body = [
            'code' => $code,
            'errors' => $message,
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
        return new AuthorizationRequestValidator();
    }
}
