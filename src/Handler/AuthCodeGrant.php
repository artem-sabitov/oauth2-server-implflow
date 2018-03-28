<?php

declare(strict_types=1);

namespace OAuth2\Handler;

use OAuth2\ClientInterface;
use OAuth2\Exception\ParameterException;
use OAuth2\Exception\RuntimeException;
use OAuth2\IdentityInterface;
use OAuth2\Provider\ClientProviderInterface;
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
    public const SUPPORTED_GRANT_TYPE = 'authorization_code';
    public const SUPPORTED_RESPONSE_TYPE = 'code';
    public const DEFAULT_TOKEN_TYPE = 'Bearer';
    public const AUTHORIZATION_CODE_KEY = 'code';
    public const REFRESH_TOKEN_KEY = 'refresh_token';
    public const TOKEN_TYPE_KEY = 'token_type';

    private const REFRESH_TOKEN_HASH_ALG = 'SHA256';

    /**
     * @var TokenInterface
     */
    protected $accessToken;

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
     * @var TokenRepositoryInterface
     */
    protected $tokenRepository;

    public function canHandle(AuthorizationRequest $request): bool
    {
        if ($request->get(self::GRANT_TYPE_KEY) === self::SUPPORTED_GRANT_TYPE) {
            return true;
        }

        if ($request->get(self::RESPONSE_TYPE_KEY) === self::AUTHORIZATION_GRANT) {
            return true;
        }

        return false;
    }

    public function handle(IdentityInterface $user, AuthorizationRequest $request): ResponseInterface
    {
        $this->validate($request);

        $this->request = $request;
        $this->user = $user;
        $this->client = $this->getClientById($this->request->getClientId());

        if ($request->getResponseType() === self::AUTHORIZATION_GRANT) {
            return $this->handlePartOne();
        }

        if ($request->get(self::GRANT_TYPE_KEY) === self::SUPPORTED_GRANT_TYPE) {
            $code = $request->get(self::AUTHORIZATION_CODE_KEY);
            $authorizationCode = $this->tokenRepository->authorizeByCode($code);

            return $this->handlePartTwo($authorizationCode);
        }

        throw new RuntimeException("Handler {self::class} can not process authorization request");
    }

    protected function handlePartOne() : ResponseInterface
    {
        /** @var AuthorizationCode $authorizationCode */
        $authorizationCode = $this->generateAuthorizationCode();
        $this->tokenRepository->write($authorizationCode);

        $redirectUri = $this->generateRedirectUri($authorizationCode);

        return new RedirectResponse($redirectUri);
    }

    protected function handlePartTwo(AuthorizationCode $authorizationCode) : ResponseInterface
    {
        // TODO @artem_sabitov implement authorization_code

        $accessToken = $this->generateAccessToken();
        $this->tokenRepository->write($accessToken);

        $refreshToken = $this->generateRefreshToken($accessToken);
        $this->tokenRepository->write($refreshToken);

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
     * @throws ParameterException
     */
    protected function validate(AuthorizationRequest $request): void
    {
        // TODO @artem_sabitov implements method!
    }

    protected function generateAuthorizationCode(): TokenInterface
    {
        $tokenBuilder = new TokenBuilder();
        $authorizationCode = $tokenBuilder
            ->setTokenClass(AuthorizationCode::class)
            ->setIdentity($this->user)
            ->setClient($this->client)
            ->setExpirationTime($this->config['expiration_time'])
            ->setIssuerIdentifier($this->config['issuer_identifier'])
            ->generate();

        return $authorizationCode;
    }

    protected function generateAccessToken(): TokenInterface
    {
        $tokenBuilder = new TokenBuilder();
        $this->accessToken = $tokenBuilder
            ->setTokenClass(AccessToken::class)
            ->setIdentity($this->user)
            ->setClient($this->client)
            ->setExpirationTime($this->config['expiration_time'])
            ->setIssuerIdentifier($this->config['issuer_identifier'])
            ->generate();

        return $this->accessToken;
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
