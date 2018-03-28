<?php

declare(strict_types=1);

namespace OAuth2;

use OAuth2\Exception;
use OAuth2\Exception\ParameterException;
use OAuth2\Exception\RuntimeException;
use OAuth2\Handler\AuthorizationHandlerInterface;
use OAuth2\Request\AuthorizationRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Expressive\Authentication\UserInterface;

class Server implements ServerInterface
{
    const INTERNAL_ERROR_MESSAGE = 'Server encountered an unexpected error while trying to process the request.';

    /**
     * array
     */
    protected $config;

    /**
     * @var array
     */
    protected $handlers = [];

    /**
     * @var callable
     */
    protected $responseFactory;

    /**
     * Server constructor.
     * @param array $config
     */
    public function __construct(array $config, callable $responseFactory)
    {
        $this->config = $config;

        $this->responseFactory = function () use ($responseFactory) : ResponseInterface {
            return $responseFactory();
        };
    }

    public function registerHandler(string $responseType, AuthorizationHandlerInterface $handler): ServerInterface
    {
        if (isset($this->handlers[$responseType])) {
            throw new RuntimeException(sprintf(
                'Cannot register %s handler; response type %s is registered',
                get_class($handler), $responseType
            ));
        }

        $this->handlers[$responseType] = $handler;

        return $this;
    }

    /**
     * @param UserInterface $user
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception\RuntimeException
     */
    public function authorize(?UserInterface $user, ServerRequestInterface $request): ResponseInterface
    {
        if (! $this->isAuthenticated($user)) {
            return new Response\RedirectResponse($this->getAuthenticationUri());
        }

        if (! $user instanceof IdentityInterface) {
            throw new Exception\RuntimeException(sprintf(
                'User instance must implement the %s interface', IdentityInterface::class
            ));
        }

        $authorizationRequest = $this->createAuthorizationRequest($request);

        try {
            $handler = $this->getHandler($authorizationRequest);
            $response = $handler->handle($user, $authorizationRequest);
        } catch (ParameterException $e) {
            return $this->createErrorResponse(400, $e->getMessages());
        }

        return $response;
    }

    public function isAuthenticated(?UserInterface $user): bool
    {
        return $user instanceof UserInterface;
    }

    public function getAuthenticationUri(): string
    {
        return $this->config['authentication_uri'];
    }

    public function createAuthorizationRequest(ServerRequestInterface $request): AuthorizationRequest
    {
        return new AuthorizationRequest($request);
    }

    /**
     * @throws ParameterException
     */
    public function getHandler(AuthorizationRequest $request): AuthorizationHandlerInterface
    {
        if (count($this->handlers) === 0) {
            throw new Exception\InvalidConfigException(
                'No authorization handlers registered'
            );
        }

        /** @var AuthorizationHandlerInterface $handler */
        foreach ($this->handlers as $handler) {
            if ($handler->canHandle($request)) {
                return $handler;
            }
        }

        throw (new ParameterException())->withMessages([
            AuthorizationRequest::RESPONSE_TYPE_KEY => 'Unsupported response type'
        ]);
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
}
