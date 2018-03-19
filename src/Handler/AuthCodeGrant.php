<?php

declare(strict_types=1);

namespace OAuth2\Handler;

use OAuth2\Exception\RuntimeException;
use OAuth2\Request\AuthorizationRequest;
use OAuth2\Token\AccessToken;
use OAuth2\Token\AuthorizationCode;
use OAuth2\Token\TokenGenerator;
use Psr\Http\Message\UriInterface;
use Zend\Diactoros\Uri;

class AuthCodeGrant extends AbstractAuthorizationHandler
{
    public const AUTHORIZATION_GRANT = 'code';

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
     * @var AuthorizationCode
     */
    protected $authorizationCode;

    public function handle(AuthorizationRequest $request): AbstractAuthorizationHandler
    {
        $this->request = $request;

        if ($request->getResponseType() === self::AUTHORIZATION_GRANT) {
            $this->authorizationCode = $this->generateAuthorizationCode();
        }
    }

    protected function generateAuthorizationCode(): AuthorizationCode
    {
        $this->authorizationCode = TokenGenerator::generate(
            AuthorizationCode::class,
            $this->getIdentity(),
            $this->getClientById($this->request->getClientId())
        );

        return $this->authorizationCode;
    }

    protected function generateAccessToken(): AccessToken
    {
        // TODO: Implement generateAccessToken() method.
    }

    protected function generateRedirectUri(): UriInterface
    {
        $redirectUri = null;

        if ($this->hasAuthorizationCode()) {
            $redirectUri = $this->authorizationCode->getClient()->getRedirectUri();
            $query = http_build_query([
                $this->options->getAccessTokenQueryKey() => $this->accessToken->getValue()
            ]);
        }

        if ($this->hasAccessToken()) {
            $redirectUri = $this->accessToken->getClient()->getRedirectUri();
            $query = http_build_query([
                $this->options->getAccessTokenQueryKey() => $this->accessToken->getValue()
            ]);
        }

        if ($redirectUri === null) {
            throw new RuntimeException('Can not generate redirect_uri without \'code\' or \'access_token\'');
        }

        return (new Uri($redirectUri))->withQuery($query);
    }

    protected function hasAuthorizationCode(): bool
    {
        return $this->authorizationCode instanceof AuthorizationCode;
    }

    protected function hasAccessToken(): bool
    {
        return $this->accessToken instanceof AccessToken;
    }
}
