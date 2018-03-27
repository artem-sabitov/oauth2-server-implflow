<?php

declare(strict_types=1);

namespace OAuth2\Handler;

use OAuth2\Exception\ParameterException;
use OAuth2\IdentityInterface;
use OAuth2\Provider\ClientProviderInterface;
use OAuth2\Provider\IdentityProviderInterface;
use OAuth2\Request\AuthorizationRequest;
use OAuth2\Storage\AccessTokenStorageInterface;
use OAuth2\Token\AccessToken;
use OAuth2\Token\TokenGenerator;
use OAuth2\Validator\AuthorizationRequestValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Uri;
use Zend\Expressive\Authentication\UserInterface;

class ImplicitGrant extends AbstractAuthorizationHandler implements AuthorizationHandlerInterface
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

    /**
     * @var IdentityInterface
     */
    protected $user;

    /**
     * @var callable
     */
    protected $responseFactory;

    public function handle(IdentityInterface $user, AuthorizationRequest $request): ResponseInterface
    {
        $validator = $this->getAuthorizationRequestValidator();
        if ($validator->validate($request) === false) {
            $messages = $validator->getErrorMessages();
            throw ParameterException::create($messages);
        }

        $this->request = $request;
        $this->user = $user;
        $this->accessToken = $this->generateAccessToken();
        $this->redirectUri = $this->generateRedirectUri();

        try {
            $this->accessTokenStorage->write($this->accessToken);
        } catch (ParameterException $e) {
            $this->responseData = $e->getMessages();
        }

        return new RedirectResponse($this->redirectUri);
    }

    public function canHandle(AuthorizationRequest $request): bool
    {
        $responseType = $request->get(self::RESPONSE_TYPE_KEY);

        return $responseType === self::AUTHORIZATION_GRANT;
    }

    protected function generateAccessToken(): AccessToken
    {
        $this->accessToken = TokenGenerator::generate(
            AccessToken::class,
            $this->user,
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
            self::ACCESS_TOKEN_KEY => $this->accessToken->getValue()
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
