<?php

declare(strict_types=1);

namespace OAuth2\Handler;

use OAuth2\Exception\ParameterException;
use OAuth2\Exception\RuntimeException;
use OAuth2\Request\AuthorizationRequest;
use OAuth2\Token\AccessToken;
use OAuth2\Token\AuthorizationCode;
use OAuth2\Token\RefreshToken;
use OAuth2\Token\TokenGenerator;
use OAuth2\Validator\AuthorizationRequestValidator;
use Psr\Http\Message\UriInterface;
use Zend\Diactoros\Uri;

class AuthCodeGrant extends AbstractAuthorizationHandler
{
    public const AUTHORIZATION_GRANT = 'code';
    public const SUPPORTED_GRANT_TYPE = 'authorization_code';
    public const SUPPORTED_RESPONSE_TYPE = 'code';
    public const DEFAULT_TOKEN_TYPE = 'Bearer';

    public const ACCESS_TOKEN_KEY = 'access_token';
    public const REFRESH_TOKEN_KEY = 'refresh_token';
    public const TOKEN_TYPE_KEY = 'token_type';
    public const EXPIRES_IN_KEY = 'expires_in';
    public const EXPIRES_ON_KEY = 'expires_on';
    public const GRANT_TYPE_KEY = 'grant_type';
    public const CLIENT_ID_KEY = 'client_id';
    public const CLIENT_SECRET_KEY = 'client_secret';
    public const CODE_KEY = 'code';

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
        $this->validate($request);

        $this->request = $request;

        if ($request->getResponseType() === self::AUTHORIZATION_GRANT) {
            $this->authorizationCode = $this->generateAuthorizationCode();
            $this->redirectUri = $this->generateRedirectUri();

            $this->responseData = [
                'headers' => [
                    self::HEADER_LOCATION => $this->redirectUri
                ]
            ];

            return $this;
        }

        if ($request->get(self::GRANT_TYPE_KEY) === self::SUPPORTED_GRANT_TYPE) {
            $accessToken = $this->requestAccessTokenByCode($request);
            $refreshToken = $this->generateRefreshToken($accessToken);
            $this->responseData = [
                'payload' => [
                    self::ACCESS_TOKEN_KEY => $accessToken->getValue(),
                    self::REFRESH_TOKEN_KEY => $refreshToken->getValue(),
                    self::TOKEN_TYPE_KEY => self::DEFAULT_TOKEN_TYPE,
                    self::EXPIRES_IN_KEY => $accessToken->getExpires(),
                    self::EXPIRES_ON_KEY => (new \DateTime())->getTimestamp() + $accessToken->getExpires(),
                ],
            ];

            return $this;
        }

        throw new RuntimeException("Handler {self::class} can not process authorization request");
    }

    public function canHandle(AuthorizationRequest $request): bool
    {
        $grantType = $request->get(self::GRANT_TYPE_KEY);
        $responseType = $request->get(self::RESPONSE_TYPE_KEY);

        if ($grantType === self::SUPPORTED_GRANT_TYPE) {
            return true;
        }

        return $responseType === self::SUPPORTED_RESPONSE_TYPE;
    }

    /**
     * @throws ParameterException
     */
    protected function validate(AuthorizationRequest $request): void
    {
        // TODO @artem_sabitov implements method!
    }

    protected function requestAccessTokenByCode(AuthorizationRequest $request): AccessToken
    {
        $clientId = $request->get(self::CLIENT_ID_KEY);
        $clientSecret = $request->get(self::CLIENT_SECRET_KEY);
        $redirectUri = $request->get(self::REDIRECT_URI_KEY);
        $code = $request->get(self::CODE_KEY);

        // TODO @artem_sabitov authorization code grant implements!

        return $this->generateAccessToken();
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
        $this->accessToken = TokenGenerator::generate(
            AccessToken::class,
            $this->getIdentity(),
            $this->getClientById($this->request->getClientId())
        );

        return $this->accessToken;
    }

    protected function generateRefreshToken(AccessToken $accessToken): RefreshToken
    {
        $tokenString = TokenGenerator::generateAccessTokenString(
            $this->getIdentity(),
            $this->getClientById($this->request->getClientId())
        );
        $expires = (int) TokenGenerator::generateExpiresAt();

        return new RefreshToken($tokenString, $accessToken, $expires);
    }

    protected function generateRedirectUri(): UriInterface
    {
        $redirectUri = null;
        $query = [];

        if ($this->hasAuthorizationCode()) {
            $redirectUri = $this->authorizationCode->getClient()->getRedirectUri();
            $query = http_build_query([
                $this->options->getAuthorizationCodeQueryKey() => $this->authorizationCode->getValue()
            ]);
        }

        if ($this->hasAccessToken()) {
            $redirectUri = $this->accessToken->getClient()->getRedirectUri();
            $query = http_build_query([
                $this->options->getAuthorizationCodeQueryKey() => $this->accessToken->getValue()
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

    public function getAuthorizationRequestValidator(): AuthorizationRequestValidator
    {
        return new AuthorizationRequestValidator();
    }
}
