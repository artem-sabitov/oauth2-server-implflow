<?php

declare(strict_types=1);

namespace OAuth2\Handler;

use OAuth2\Exception\ParameterException;
use OAuth2\Request\AuthorizationRequest;
use OAuth2\Token\AccessToken;
use OAuth2\Token\TokenGenerator;
use Psr\Http\Message\UriInterface;
use Zend\Diactoros\Uri;

class ImplicitGrant extends AbstractAuthorizationHandler
{
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

    public function handle(AuthorizationRequest $request): AbstractAuthorizationHandler
    {
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
}
