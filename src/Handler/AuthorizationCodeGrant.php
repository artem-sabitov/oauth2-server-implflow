<?php

declare(strict_types=1);

namespace OAuth2\Handler;

use OAuth2\ClientInterface;
use OAuth2\Exception\ParameterException;
use OAuth2\Exception\RuntimeException;
use OAuth2\IdentityInterface;
use OAuth2\Repository\AccessTokenRepositoryInterface;
use OAuth2\Repository\AuthorizationCodeRepositoryInterface;
use OAuth2\Repository\ClientRepositoryInterface;
use OAuth2\Repository\RefreshTokenRepositoryInterface;
use OAuth2\Request\AuthorizationRequest;
use OAuth2\Token\AccessToken;
use OAuth2\Token\AuthorizationCode;
use OAuth2\Token\RefreshToken;
use OAuth2\Token\TokenBuilder;
use OAuth2\Token\TokenInterface;
use OAuth2\UriBuilder;
use OAuth2\Validator\AccessTokenRequestValidator;
use OAuth2\Validator\AuthorizationCodeRequestValidator;
use OAuth2\Validator\RefreshTokenRequestValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Uri;

class AuthorizationCodeGrant extends AbstractAuthorizationHandler
{
    public const AUTHORIZATION_GRANT = 'code';
    public const DEFAULT_TOKEN_TYPE = 'Bearer';
    public const TOKEN_TYPE_KEY = 'token_type';
    public const GRANT_TYPE_KEY = 'grant_type';
    public const AUTHORIZATION_CODE = 'authorization_code';
    public const AUTHORIZATION_CODE_KEY = 'code';
    public const REFRESH_TOKEN = 'refresh_token';
    public const REFRESH_TOKEN_KEY = 'refresh_token';

    private const REFRESH_TOKEN_HASH_ALG = 'SHA256';

    /**
     * @var TokenInterface
     */
    private $accessToken;

    /**
     * @var AuthorizationRequest
     */
    private $request;

    /**
     * @var IdentityInterface
     */
    private $user;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var AuthorizationCodeRepositoryInterface
     */
    private $codeRepository;

    /**
     * @var RefreshTokenRepositoryInterface
     */
    private $refreshTokenRepository;

    public function __construct(
        array $config,
        ClientRepositoryInterface $clientRepository,
        AccessTokenRepositoryInterface $accessTokenRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        AuthorizationCodeRepositoryInterface $codeRepository
    ) {
        $this->codeRepository = $codeRepository;
        $this->refreshTokenRepository = $refreshTokenRepository;
        parent::__construct($config, $clientRepository, $accessTokenRepository);
    }

    protected function getAuthenticationUri() : Uri
    {
        return new Uri($this->config['authentication_uri']);
    }

    public function canHandle(AuthorizationRequest $request) : bool
    {
        return $this->isRequestAuthorizationCode($request) ||
            $this->isRequestAccessTokenByCode($request) ||
            $this->isRequestRefreshToken($request);
    }

    private function isRequestAuthorizationCode(AuthorizationRequest $request) : bool
    {
        if ($request->getMethod() !== 'GET') {
            return false;
        }

        return $request->get(self::RESPONSE_TYPE_KEY) === self::AUTHORIZATION_GRANT;
    }

    private function isRequestAccessTokenByCode(AuthorizationRequest $request) : bool
    {
        if ($request->getMethod() !== 'POST') {
            return false;
        }

        return $request->get(self::GRANT_TYPE_KEY) === self::AUTHORIZATION_CODE;
    }

    private function isRequestRefreshToken(AuthorizationRequest $request) : bool
    {
        if ($request->getMethod() !== 'POST') {
            return false;
        }

        return $request->get(self::GRANT_TYPE_KEY) === self::REFRESH_TOKEN;
    }

    public function handle(?IdentityInterface $user, AuthorizationRequest $request) : ResponseInterface
    {
        $this->request = $request;

        $this->client = $this->clientRepository->find($request->getClientId());
        if ($this->client === null) {
            throw (new ParameterException())->withMessages([
                self::CLIENT_ID_KEY =>
                    'The provided client_id cannot be used'
            ]);
        }

        if ($this->isRequestAuthorizationCode($request)) {
            if (! $user instanceof IdentityInterface) {
                return new RedirectResponse($this->getAuthenticationUri());
            }
            $this->user = $user;

            $validator = $this->getAuthorizationCodeRequestValidator();
            if ($validator->validate($request) === false) {
                $messages = $validator->getErrorMessages();
                throw ParameterException::create($messages);
            }

            return $this->handleRequestAuthorizationCode();
        }

        if ($this->isRequestAccessTokenByCode($request)) {
            $validator = $this->getAccessTokenRequestValidator();
            if ($validator->validate($request) === false) {
                $messages = $validator->getErrorMessages();
                throw ParameterException::create($messages);
            }

            $code = $request->get(self::AUTHORIZATION_CODE_KEY);
            $authorizationCode = $this->codeRepository->find($code);
            if ($authorizationCode === null) {
                throw (new ParameterException())->withMessages([
                    self::AUTHORIZATION_CODE_KEY =>
                        'The provided authorization code cannot be used'
                ]);
            }

            return $this->handleRequestAccessTokenByCode($authorizationCode);
        }

        if ($this->isRequestRefreshToken($request)) {
            $validator = $this->getRefreshTokenRequestValidator();
            if ($validator->validate($request) === false) {
                $messages = $validator->getErrorMessages();
                throw ParameterException::create($messages);
            }

            $refreshToken = $this->refreshTokenRepository->find(
                $request->get(self::REFRESH_TOKEN_KEY)
            );
            if ($refreshToken === null) {
                throw (new ParameterException())->withMessages([
                    self::REFRESH_TOKEN_KEY =>
                        'The provided refresh token cannot be used'
                ]);
            }

            return $this->handleRequestRefreshToken($refreshToken);
        }

        throw new RuntimeException(sprintf(
            'Handler \'%s\' can not process authorization request',
            self::class
        ));
    }

    private function getAuthorizationCodeRequestValidator() : AuthorizationCodeRequestValidator
    {
        return new AuthorizationCodeRequestValidator();
    }

    private function getAccessTokenRequestValidator() : AccessTokenRequestValidator
    {
        return new AccessTokenRequestValidator();
    }

    private function getRefreshTokenRequestValidator() : RefreshTokenRequestValidator
    {
        return new RefreshTokenRequestValidator();
    }

    protected function handleRequestAuthorizationCode() : ResponseInterface
    {
        $code = $this->generateAuthorizationCode($this->user, $this->client);
        $code = $this->codeRepository->write($code);

        return new RedirectResponse(
            $this->generateRedirectUri($code)
        );
    }

    protected function handleRequestAccessTokenByCode(AuthorizationCode $authorizationCode) : ResponseInterface
    {
        $this->validateAuthorizationCode($authorizationCode);

        $accessToken = $this->generateAccessToken(
            $authorizationCode->getIdentity(), $this->client
        );
        $this->accessTokenRepository->write($accessToken);

        $refreshToken = $this->generateRefreshToken($accessToken);
        $this->refreshTokenRepository->write($refreshToken);

        $payload = [
            self::ACCESS_TOKEN_KEY => $accessToken->getValue(),
            self::REFRESH_TOKEN_KEY => $refreshToken->getValue(),
            self::TOKEN_TYPE_KEY => self::DEFAULT_TOKEN_TYPE,
            self::EXPIRES_IN_KEY => $accessToken->getExpires() - (new \DateTime())->getTimestamp(),
            self::EXPIRES_ON_KEY => $accessToken->getExpires(),
        ];

        $authorizationCode->setUsed(true);
        $this->codeRepository->write($authorizationCode);

        return new JsonResponse($payload);
    }

    protected function handleRequestRefreshToken(RefreshToken $refreshToken) : ResponseInterface
    {
        $this->validateRefreshToken($refreshToken);

        $accessToken = $this->generateAccessToken(
            $refreshToken->getIdentity(), $this->client
        );
        $accessToken = $this->accessTokenRepository->write($accessToken);

        $refreshToken = $this->generateRefreshToken($accessToken);
        $refreshToken = $this->refreshTokenRepository->write($refreshToken);

        $payload = [
            self::ACCESS_TOKEN_KEY => $accessToken->getValue(),
            self::REFRESH_TOKEN_KEY => $refreshToken->getValue(),
            self::TOKEN_TYPE_KEY => self::DEFAULT_TOKEN_TYPE,
            self::EXPIRES_IN_KEY => $accessToken->getExpires() - (new \DateTime())->getTimestamp(),
            self::EXPIRES_ON_KEY => $accessToken->getExpires(),
        ];

        $refreshToken->setUsed(true);
        $this->refreshTokenRepository->write($refreshToken);

        return new JsonResponse($payload);
    }

    /**
     * throws Exception\ParameterException
     */
    private function validateAuthorizationCode(AuthorizationCode $code) : void
    {
        if ($code->isUsed()) {
            throw (new ParameterException())->withMessages([
                self::AUTHORIZATION_CODE_KEY =>
                    'The provided authorization code is already used'
            ]);
        }

        $now = (new \DateTime())->getTimestamp();
        if ($code->getExpires() <= $now) {
            throw (new ParameterException())->withMessages([
                self::AUTHORIZATION_CODE_KEY =>
                    'The provided authorization code is expired'
            ]);
        }
    }

    private function validateRefreshToken(RefreshToken $refreshToken) : void
    {
        if ($refreshToken->isUsed()) {
            throw (new ParameterException())->withMessages([
                self::REFRESH_TOKEN_KEY =>
                    'The provided refresh token is already used'
            ]);
        }

        $now = (new \DateTime())->getTimestamp();
        if ($refreshToken->getExpires() <= $now) {
            throw (new ParameterException())->withMessages([
                self::REFRESH_TOKEN_KEY =>
                    'The provided refresh token is expired'
            ]);
        }
    }

    /**
     * @throws ParameterException
     */
    protected function validate(AuthorizationRequest $request) : void
    {
        // TODO @artem_sabitov implements method!
    }

    protected function generateAuthorizationCode(IdentityInterface $user, ClientInterface $client) : AuthorizationCode
    {
        $tokenBuilder = new TokenBuilder();

        /** @var AuthorizationCode $authorizationCode */
        $authorizationCode = $tokenBuilder
            ->setTokenClass(AuthorizationCode::class)
            ->setIdentity($user)
            ->setClient($client)
            ->setExpirationTime($this->config['expiration_time'])
            ->setIssuerIdentifier($this->config['issuer_identifier'])
            ->generate();

        return $authorizationCode;
    }

    protected function generateAccessToken(IdentityInterface $user, ClientInterface $client) : AccessToken
    {
        $tokenBuilder = new TokenBuilder();

        /** @var AccessToken $accessToken */
        $accessToken = $tokenBuilder
            ->setTokenClass(AccessToken::class)
            ->setIdentity($user)
            ->setClient($client)
            ->setExpirationTime($this->config['expiration_time'])
            ->setIssuerIdentifier($this->config['issuer_identifier'])
            ->generate();

        return $accessToken;
    }

    protected function generateRefreshToken(AccessToken $accessToken) : RefreshToken
    {
        $expires = $accessToken->getExpires() + $this->config['refresh_token_extra_time'];
        $expires = (new \DateTime())
            ->setTimestamp($expires)
            ->getTimestamp();

        $refreshToken = new RefreshToken(
            hash(self::REFRESH_TOKEN_HASH_ALG, $accessToken->getValue()),
            $accessToken,
            $expires
        );

        return $refreshToken;
    }

    protected function generateRedirectUri(AuthorizationCode $authorizationCode) : UriInterface
    {
        $requestedRedirectUri = '';
        if ($this->request instanceof AuthorizationRequest) {
            $requestedRedirectUri = $this->request->get(self::REDIRECT_URI_KEY);
        }

        $query = http_build_query([
            self::AUTHORIZATION_CODE_KEY => $authorizationCode->getValue(),
            self::EXPIRES_ON_KEY => $authorizationCode->getExpires(),
        ]);

        $redirectUri = $authorizationCode->getClient()->getRedirectUri();

        if (strpos($requestedRedirectUri, $redirectUri) === false) {
            throw (new ParameterException())->withMessages([
                self::REDIRECT_URI_KEY => sprintf(
                    "Uri %s can not register for client %s",
                    $requestedRedirectUri,
                    $authorizationCode->getClient()->getClientId()
                )
            ]);
        }

        $uri = (new UriBuilder())
            ->setAllowedSchemes($this->config['allowed_schemes'])
            ->build($redirectUri);

        return $uri->withQuery($query);
    }
}
