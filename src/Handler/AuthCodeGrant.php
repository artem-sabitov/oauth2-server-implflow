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
use OAuth2\Repository\RefreshTokenRepositoryInterface;
use OAuth2\Request\AuthorizationRequest;
use OAuth2\Token\AccessToken;
use OAuth2\Token\TokenInterface;
use OAuth2\Token\AuthorizationCode;
use OAuth2\Token\RefreshToken;
use OAuth2\Token\TokenBuilder;
use OAuth2\TokenRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Uri;

class AuthCodeGrant extends AbstractAuthorizationHandler implements AuthorizationHandlerInterface
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
        ClientProviderInterface $clientProvider,
        AccessTokenRepositoryInterface $accessTokenRepository,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
        AuthorizationCodeRepositoryInterface $codeRepository
    ) {
        $this->codeRepository = $codeRepository;
        $this->refreshTokenRepository = $refreshTokenRepository;
        parent::__construct($config, $clientProvider, $accessTokenRepository);
    }

    public function canHandle(AuthorizationRequest $request): bool
    {
        return $this->isRequestAuthorizationCode($request) ||
            $this->isRequestAccessTokenByCode($request);
    }

    private function isRequestAuthorizationCode(AuthorizationRequest $request): bool
    {
        return $request->get(self::RESPONSE_TYPE_KEY) === self::AUTHORIZATION_GRANT;
    }

    private function isRequestAccessTokenByCode(AuthorizationRequest $request): bool
    {
        return $request->get(self::GRANT_TYPE_KEY) === self::SUPPORTED_GRANT_TYPE;
    }

    public function handle(IdentityInterface $user, AuthorizationRequest $request): ResponseInterface
    {
        $this->validate($request);

        $this->request = $request;
        $this->user = $user;
        $this->client = $this->getClientById($this->request->getClientId());

        if ($this->isRequestAuthorizationCode($request)) {
            return $this->handlePartOne();
        }

        if ($this->isRequestAccessTokenByCode($request)) {
            $code = $request->get(self::AUTHORIZATION_CODE_KEY);
            if ($code === null) {
                throw (new ParameterException())->withMessages([
                    self::AUTHORIZATION_CODE_KEY =>
                        'Authorization code was not provided'
                ]);
            }

            return $this->handlePartTwo($code);
        }

        throw new RuntimeException(sprintf(
            'Handler \'%s\' can not process authorization request',
            self::class
        ));
    }

    protected function handlePartOne() : ResponseInterface
    {
        $code = $this->generateAuthorizationCode();
        $this->codeRepository->write($code);

        return new RedirectResponse(
            $this->generateRedirectUri($code)
        );
    }

    protected function handlePartTwo(string $code) : ResponseInterface
    {
        $code = $this->codeRepository->find($code);
        $this->validateAuthorizationCode($code);

        $accessToken = $this->generateAccessToken();
        $this->accessTokenRepository->write($accessToken);

        $refreshToken = $this->generateRefreshToken($accessToken);
        $this->refreshTokenRepository->write($refreshToken);

        $payload = [
            self::ACCESS_TOKEN_KEY => $accessToken->getValue(),
            self::REFRESH_TOKEN_KEY => $refreshToken->getValue(),
            self::TOKEN_TYPE_KEY => self::DEFAULT_TOKEN_TYPE,
            self::EXPIRES_IN_KEY => $accessToken->getExpires(),
            self::EXPIRES_ON_KEY => (new \DateTime())->getTimestamp() + $accessToken->getExpires(),
        ];

        return new JsonResponse($payload);
    }

    /**
     * throws Exception\ParameterException
     */
    private function validateAuthorizationCode(?AuthorizationCode $code): void
    {
        if ($code === null) {
            throw (new ParameterException())->withMessages([
                self::AUTHORIZATION_CODE_KEY =>
                    'The provided authorization code cannot be used'
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

    protected function generateRefreshToken(TokenInterface $accessToken): RefreshToken
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
        $redirectUri = $authorizationCode->getClient()->getRedirectUri();
        $query = http_build_query([
            self::AUTHORIZATION_CODE_KEY => $authorizationCode->getValue(),
            // TODO @artem_sabitov return state from request
        ]);

        return (new Uri($redirectUri))->withQuery($query);
    }
}
