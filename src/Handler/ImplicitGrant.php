<?php

declare(strict_types=1);

namespace OAuth2\Handler;

use OAuth2\Exception\ParameterException;
use OAuth2\Provider\ClientProviderInterface;
use OAuth2\Provider\IdentityProviderInterface;
use OAuth2\Request\AuthorizationRequest;
use OAuth2\Storage\AccessTokenStorageInterface;
use OAuth2\Token\AccessToken;
use OAuth2\Token\TokenGenerator;
use OAuth2\Validator\AuthorizationRequestValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Zend\Diactoros\Uri;
use Zend\Expressive\Authentication\UserInterface;

class ImplicitGrant extends AbstractAuthorizationHandler
{
    public const AUTHORIZATION_GRANT = 'token';

    /**
     * @var AccessToken
     */
    protected $accessToken;

    /**
     * @var UriInterface
     */
    protected $redirectUri;

    /**
     * @var AuthorizationRequest
     */
    protected $request;

    public function __construct(
        array $config,
        ClientProviderInterface $clientProvider,
        AccessTokenStorageInterface $accessTokenStorage,
        callable $responseFactory
    ) {
        parent::__construct($config, $clientProvider, $accessTokenStorage);
    }

    public function handle(UserInterface $user, AuthorizationRequest $request): ResponseInterface
    {
        $validator = $this->getAuthorizationRequestValidator();
        if ($validator->validate($request) === false) {
            $messages = $validator->getErrorMessages();
            throw ParameterException::create($messages);
        }

        $this->request = $request;
        $this->accessToken = $this->generateAccessToken();
        $this->redirectUri = $this->generateRedirectUri();

        try {
            $this->accessTokenStorage->write($this->accessToken);
        } catch (ParameterException $e) {
            $this->responseData = $e->getMessages();
        }

        $this->responseData = [
            'headers' => [
                self::HEADER_LOCATION => $this->redirectUri
            ]
        ];

        return $this;
    }

    public function canHandle(AuthorizationRequest $request): bool
    {
        $responseType = $request->get(self::RESPONSE_TYPE_KEY);

        return $responseType === self::SUPPORTED_RESPONSE_TYPE;
    }

    protected function generateAccessToken(): AccessToken
    {
        $this->accessToken = TokenGenerator::generate(
            AccessToken::class,
            $this->getIdentity(),
            $this->getClientById($this->request->getClientId())
        );

        return $this->accessToken;
    }

    protected function generateRedirectUri(): UriInterface
    {
        if (! $this->hasGeneratedAccessToken()) {
            throw new ParameterException();
        }

        $redirectUri = $this->accessToken->getClient()->getRedirectUri();
        $query = http_build_query([
            $this->options->getAccessTokenQueryKey() => $this->accessToken->getValue()
        ]);

        return (new Uri($redirectUri))->withQuery($query);
    }

    protected function hasGeneratedAccessToken(): bool
    {
        return $this->accessToken instanceof AccessToken;
    }

    public function getAuthorizationRequestValidator(): AuthorizationRequestValidator
    {
        return new AuthorizationRequestValidator();
    }
}
