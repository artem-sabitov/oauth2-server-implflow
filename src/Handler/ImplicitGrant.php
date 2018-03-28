<?php

declare(strict_types=1);

namespace OAuth2\Handler;

use OAuth2\ClientInterface;
use OAuth2\Exception\ParameterException;
use OAuth2\IdentityInterface;
use OAuth2\Request\AuthorizationRequest;
use OAuth2\Token\AccessToken;
use OAuth2\Token\TokenInterface;
use OAuth2\Token\TokenBuilder;
use OAuth2\Validator\AuthorizationRequestValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Uri;

class ImplicitGrant extends AbstractAuthorizationHandler implements AuthorizationHandlerInterface
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

    public function handle(IdentityInterface $user, AuthorizationRequest $request): ResponseInterface
    {
        $validator = $this->getAuthorizationRequestValidator();
        if ($validator->validate($request) === false) {
            $messages = $validator->getErrorMessages();
            throw ParameterException::create($messages);
        }

        $this->request = $request;
        $this->user = $user;
        $this->client = $this->getClientById($this->request->getClientId());

        $accessToken = $this->generateAccessToken();
        $this->tokenRepository->write($accessToken);

        $this->redirectUri = $this->generateRedirectUri($accessToken);

        return new RedirectResponse($this->redirectUri);
    }

    public function canHandle(AuthorizationRequest $request): bool
    {
        $responseType = $request->get(self::RESPONSE_TYPE_KEY);

        return $responseType === self::AUTHORIZATION_GRANT;
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
        $query = [
            self::ACCESS_TOKEN_KEY => $accessToken->getValue(),
            self::EXPIRES_IN_KEY => $accessToken->getExpires(),
            self::EXPIRES_ON_KEY => (new \DateTime())->getTimestamp() + $accessToken->getExpires(),
        ];

        $redirectUri = $accessToken->getClient()->getRedirectUri();

        return (new Uri($redirectUri))->withQuery(http_build_query($query));
    }

    public function getAuthorizationRequestValidator(): AuthorizationRequestValidator
    {
        return new AuthorizationRequestValidator();
    }
}
