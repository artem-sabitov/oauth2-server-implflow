<?php

declare(strict_types=1);

namespace OAuth2\Handler;

use OAuth2\ClientInterface;
use OAuth2\Exception;
use OAuth2\IdentityInterface;
use OAuth2\Request\AuthorizationRequest;
use OAuth2\Token\AccessToken;
use OAuth2\Token\TokenBuilder;
use OAuth2\Token\TokenInterface;
use OAuth2\UriBuilder;
use OAuth2\Validator\AuthorizationRequestValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Uri;

class ImplicitGrant extends AbstractAuthorizationHandler
{
    public const AUTHORIZATION_GRANT = 'token';

    /**
     * @var TokenInterface
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
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var callable
     */
    protected $responseFactory;

    public function handle(?IdentityInterface $user, AuthorizationRequest $request): ResponseInterface
    {
        $this->request = $request;

        $validator = $this->getAuthorizationRequestValidator();
        if ($validator->validate($request) === false) {
            $messages = $validator->getErrorMessages();
            throw Exception\ParameterException::create($messages);
        }

        if (! $user instanceof IdentityInterface) {
            return new RedirectResponse($this->getAuthenticationUri());
        }
        $this->user = $user;

        $this->client = $this->clientRepository->find($request->getClientId());
        if ($this->client === null) {
            throw (new Exception\ParameterException())->withMessages([
                self::CLIENT_ID_KEY => 'The provided client_id cannot be used'
            ]);
        }

        $accessToken = $this->generateAccessToken();
        $accessToken = $this->accessTokenRepository->write($accessToken);

        return new RedirectResponse(
            $this->generateRedirectUri($accessToken)
        );
    }

    public function canHandle(AuthorizationRequest $request): bool
    {
        if ($request->getMethod() !== 'GET') {
            return false;
        }

        return $request->get(self::RESPONSE_TYPE_KEY) === self::AUTHORIZATION_GRANT;
    }

    protected function generateAccessToken(): AccessToken
    {
        $tokenBuilder = new TokenBuilder();
        /** @var AccessToken $accessToken */
        $accessToken = $tokenBuilder
            ->setTokenClass(AccessToken::class)
            ->setIdentity($this->user)
            ->setClient($this->client)
            ->setExpirationTime($this->config['expiration_time'])
            ->setIssuerIdentifier($this->config['issuer_identifier'])
            ->generate();

        return $accessToken;
    }

    protected function generateRedirectUri(AccessToken $accessToken): UriInterface
    {
        $requestedRedirectUri = $this->request->get(self::REDIRECT_URI_KEY);
        $redirectUri = $accessToken->getClient()->getRedirectUri();

        if (strpos($requestedRedirectUri, $redirectUri) === false) {
            throw (new Exception\ParameterException())->withMessages([
                self::REDIRECT_URI_KEY => sprintf(
                    "Uri %s can not register for client %s",
                    $requestedRedirectUri,
                    $accessToken->getClient()->getClientId()
                )
            ]);
        }

        $query = http_build_query([
            self::ACCESS_TOKEN_KEY => $accessToken->getValue(),
            self::EXPIRES_IN_KEY => $this->config['expiration_time'],
            self::EXPIRES_ON_KEY => $accessToken->getExpires(),
        ]);

        return (new UriBuilder())
            ->setAllowedSchemes($this->config['allowed_schemes'])
            ->build($redirectUri)
            ->withQuery($query);
    }

    protected function getAuthenticationUri() : Uri
    {
        return new Uri($this->config['authentication_uri']);
    }

    public function getAuthorizationRequestValidator(): AuthorizationRequestValidator
    {
        return new AuthorizationRequestValidator();
    }
}
