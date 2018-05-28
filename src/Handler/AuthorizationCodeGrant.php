<?php

declare(strict_types=1);

namespace OAuth2\Handler;

use OAuth2\ClientInterface;
use OAuth2\Exception\ParameterException;
use OAuth2\Exception\RuntimeException;
use OAuth2\IdentityInterface;
use OAuth2\Provider\ClientProviderInterface;
use OAuth2\Repository\AccessTokenRepositoryInterface;
use OAuth2\Repository\AuthorizationCodeRepositoryInterface;
use OAuth2\Repository\ClientRepositoryInterface;
use OAuth2\Repository\RefreshTokenRepositoryInterface;
use OAuth2\Request\AuthorizationRequest;
use OAuth2\Token\AccessToken;
use OAuth2\Token\TokenInterface;
use OAuth2\Token\AuthorizationCode;
use OAuth2\Token\RefreshToken;
use OAuth2\Token\TokenBuilder;
use OAuth2\TokenRepositoryInterface;
use OAuth2\UriBuilder;
use OAuth2\Validator\AccessTokenRequestValidator;
use OAuth2\Validator\AuthorizationCodeRequestValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Uri;

class AuthorizationCodeGrant extends AbstractAuthorizationHandler implements AuthorizationHandlerInterface
{
    public const AUTHORIZATION_GRANT = 'code';
    public const AUTHORIZATION_CODE_KEY = 'code';
    public const DEFAULT_TOKEN_TYPE = 'Bearer';
    public const GRANT_TYPE_KEY = 'grant_type';
    public const SUPPORTED_GRANT_TYPE = 'authorization_code';
    public const TOKEN_TYPE_KEY = 'token_type';

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

    public function canHandle(AuthorizationRequest $request): bool
    {
        return $this->isRequestAuthorizationCode($request) ||
            $this->isRequestAccessTokenByCode($request);
    }

    private function isRequestAuthorizationCode(AuthorizationRequest $request): bool
    {
        if ($request->getMethod() !== 'GET') {
            return false;
        }

        return $request->get(self::RESPONSE_TYPE_KEY) === self::AUTHORIZATION_GRANT;
    }

    private function isRequestAccessTokenByCode(AuthorizationRequest $request): bool
    {
        if ($request->getMethod() !== 'POST') {
            return false;
        }

        return $request->get(self::GRANT_TYPE_KEY) === self::SUPPORTED_GRANT_TYPE;
    }

    public function handle(IdentityInterface $user, AuthorizationRequest $request): ResponseInterface
    {
        $this->request = $request;
        $this->user = $user;

        $client = $this->clientRepository->find($request->getClientId());
        if ($client === null) {
            throw (new ParameterException())->withMessages([
                self::CLIENT_ID_KEY =>
                    'The provided client_id cannot be used'
            ]);
        }
        $this->client = $client;

        if ($this->isRequestAuthorizationCode($request)) {
            $validator = $this->getAuthorizationCodeRequestValidator();
            if ($validator->validate($request) === false) {
                $messages = $validator->getErrorMessages();
                throw ParameterException::create($messages);
            }

            return $this->handlePartOne();
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

            return $this->handlePartTwo($authorizationCode);
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

    protected function handlePartOne() : ResponseInterface
    {
        $code = $this->generateAuthorizationCode();
        $code = $this->codeRepository->write($code);

        return new RedirectResponse(
            $this->generateRedirectUri($code)
        );
    }

    protected function handlePartTwo(AuthorizationCode $authorizationCode) : ResponseInterface
    {
        $this->validateAuthorizationCode($authorizationCode);

        $accessToken = $this->generateAccessToken();
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

        $authorizationCode->setUsed(true);
        $this->codeRepository->write($authorizationCode);

        return new JsonResponse($payload);
    }

    /**
     * throws Exception\ParameterException
     */
    private function validateAuthorizationCode(AuthorizationCode $code): void
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

    /**
     * @throws ParameterException
     */
    protected function validate(AuthorizationRequest $request): void
    {
        // TODO @artem_sabitov implements method!
    }

    protected function generateAuthorizationCode(): AuthorizationCode
    {
        $tokenBuilder = new TokenBuilder();

        /** @var AuthorizationCode $authorizationCode */
        $authorizationCode = $tokenBuilder
            ->setTokenClass(AuthorizationCode::class)
            ->setIdentity($this->user)
            ->setClient($this->client)
            ->setExpirationTime($this->config['expiration_time'])
            ->setIssuerIdentifier($this->config['issuer_identifier'])
            ->generate();

        return $authorizationCode;
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

    protected function generateRefreshToken(AccessToken $accessToken): RefreshToken
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

    protected function generateRedirectUri(AuthorizationCode $authorizationCode): UriInterface
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
