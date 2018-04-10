<?php

declare(strict_types=1);

namespace OAuth2Test;

use OAuth2\ClientInterface;
use OAuth2\ConfigProvider;
use OAuth2\Exception\InvalidConfigException;
use OAuth2\Handler\AuthCodeGrant;
use OAuth2\Handler\ImplicitGrant;
use OAuth2\IdentityInterface;
use OAuth2\Repository\AccessTokenRepositoryInterface;
use OAuth2\Repository\AuthorizationCodeRepositoryInterface;
use OAuth2\Repository\ClientRepositoryInterface;
use OAuth2\Repository\RefreshTokenRepositoryInterface;
use OAuth2\Request\AuthorizationRequest;
use OAuth2\Provider\IdentityProviderInterface;
use OAuth2\Server;
use OAuth2\ServerInterface;
use OAuth2\Token\AuthorizationCode;
use OAuth2\UriBuilder;
use OAuth2Test\Assets\TestClientRepository;
use OAuth2Test\Assets\TestSuccessIdentityProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest as Request;
use Zend\Diactoros\Uri;
use Zend\Expressive\Authentication\UserInterface;
use Zend\Json\Json;

class ServerTest extends TestCase
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var callable
     */
    protected $responseFactory;

    /**
     * @var IdentityProviderInterface
     */
    private $identityProvider;

    /**
     * @var ClientRepositoryInterface
     */
    private $clientRepository;

    protected function setUp()
    {
        /** @var array $config */
        $config = (new ConfigProvider())();

        $this->config = array_merge($config, [
            'authentication_uri' => 'http://example.com/login',
        ]);

        $this->responseFactory = function () : ResponseInterface {
            return new Response();
        };

        $this->identityProvider = new TestSuccessIdentityProvider();
        $this->clientRepository = new TestClientRepository();
    }

    public function getServer(): ServerInterface
    {
        return new Server($this->config, $this->responseFactory);
    }

    public function registerImplicitGrantHandler(ServerInterface $server): ServerInterface
    {
        $tokenRepository = $this->createMock(AccessTokenRepositoryInterface::class);
        $handler = new ImplicitGrant(
            [
                'expiration_time' => 60 * 60,
                'issuer_identifier' => 'test_server',
                'allowed_schemes' => [
                    'http' => 80,
                    'https' => 443,
                ],
            ],
            $this->clientRepository,
            $tokenRepository
        );

        return $server->registerHandler('token', $handler);
    }

    public function registerAuthCodeGrantHandler(ServerInterface $server): ServerInterface
    {
        $testCode = new AuthorizationCode(
            'test',
            $this->createMock(IdentityInterface::class),
            $this->createMock(ClientInterface::class),
            (new \DateTime())->modify('+1 day')->getTimestamp()
        );
        $expiredCode = new AuthorizationCode(
            'expired',
            $this->createMock(IdentityInterface::class),
            $this->createMock(ClientInterface::class),
            1522540800
        );
        $usedCode = new AuthorizationCode(
            'expired',
            $this->createMock(IdentityInterface::class),
            $this->createMock(ClientInterface::class),
            1522540800
        );
        $usedCode->setUsed(true);

        $accessTokenRepository = $this->createMock(AccessTokenRepositoryInterface::class);
        $refreshTokenRepository = $this->createMock(RefreshTokenRepositoryInterface::class);
        $codeRepository = $this->prophesize(AuthorizationCodeRepositoryInterface::class);
        $codeRepository->find('test')->willReturn($testCode);
        $codeRepository->find('expired')->willReturn($expiredCode);
        $codeRepository->find('used')->willReturn($usedCode);
        $codeRepository->find('')->willReturn(null);
        $codeRepository->find(null)->willReturn(null);
        $codeRepository->write(Argument::any())->will(function () { return; });
        $handler = new AuthCodeGrant(
            [
                'expiration_time' => 60 * 60,
                'issuer_identifier' => 'test_server',
                'refresh_token_extra_time' => 60 * 60,
                'allowed_schemes' => [
                    'http' => 80,
                    'https' => 443,
                    'testapp' => 0,
                ],
            ],
            $this->clientRepository,
            $accessTokenRepository,
            $refreshTokenRepository,
            $codeRepository->reveal()
        );

        return $server->registerHandler('code', $handler);
    }

    public function getUser(): UserInterface
    {
        return new class implements UserInterface, IdentityInterface
        {
            public function getIdentity(): string { return $this->getIdentityId(); }

            public function getUserRoles(): array { return []; }

            public function getIdentityId() { return 'test'; }
        };
    }

    public function getServerRequest(): ServerRequestInterface
    {
        return new Request(
            [],
            [],
            'http://example.com/',
            'GET',
            'php://memory',
            [],
            [],
            [
                'client_id' => 'test',
                'redirect_uri' => 'http://example.com',
                'response_type' => 'token',
            ]
        );
    }

    public function testConstructorAcceptsAnArguments(): void
    {
        $server = $this->getServer();
        $this->assertInstanceOf(Server::class, $server);
    }

    public function testInstanceImplementsServerInterface(): void
    {
        $this->assertInstanceOf(ServerInterface::class, $this->getServer());
    }

    public function testServerCanCreateAuthorizationRequestFromServerRequest(): void
    {
        $serverRequest = new Request(
            [],
            [],
            'http://example.com/',
            'GET',
            'php://memory',
            [],
            [],
            [
                'redirect_uri' => 'http://example.com',
                'response_type' => 'token',
            ]
        );

        $this->assertInstanceOf(
            AuthorizationRequest::class,
            $this->getServer()->createAuthorizationRequest($serverRequest)
        );
    }

    public function testServerThrowsExceptionWithoutRegisteredHandlers()
    {
        $serverRequest = $this->getServerRequest();
        $server = $this->getServer();
        $user = $this->getUser();

        $this->expectException(InvalidConfigException::class);
        $server->authorize($user, $serverRequest);
    }

    public function testAuthorizationWithoutClientIdReturnError(): void
    {
        $serverRequest = new Request(
            [],
            [],
            'http://example.com/',
            'GET',
            'php://memory',
            [],
            [],
            [
                'redirect_uri' => 'http://example.com',
                'response_type' => 'token',
            ]
        );

        $server = $this->registerImplicitGrantHandler($this->getServer());
        $user = $this->getUser();

        $response = $server->authorize($user, $serverRequest);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(
            "{\"code\":400,\"errors\":{\"client_id\":\"Required parameter \u0027client_id\u0027 is missing\"}}",
            $response->getBody()->getContents()
        );
    }

    public function testAuthorizationWithoutRedirectUriReturnError()
    {
        $serverRequest = new Request(
            [],
            [],
            'http://example.com/',
            'GET',
            'php://memory',
            [],
            [],
            [
                'client_id' => 'test',
                'response_type' => 'token',
            ]
        );

        $server = $this->registerImplicitGrantHandler($this->getServer());
        $user = $this->getUser();

        $response = $server->authorize($user, $serverRequest);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(
            "{\"code\":400,\"errors\":{\"redirect_uri\":\"Required parameter \u0027redirect_uri\u0027 is missing\"}}",
            $response->getBody()->getContents()
        );
    }

    public function testAuthorizationWithoutResponseTypeReturnError()
    {
        $serverRequest = new Request(
            [],
            [],
            'http://example.com/',
            'GET',
            'php://memory',
            [],
            [],
            [
                'client_id' => 'test',
                'redirect_uri' => 'http://example.com',
            ]
        );

        $server = $this->registerImplicitGrantHandler($this->getServer());
        $user = $this->getUser();

        $response = $server->authorize($user, $serverRequest);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(
            "{\"code\":400,\"errors\":{\"response_type\":\"Unsupported response type\"}}",
            $response->getBody()->getContents()
        );
    }

    public function testAuthorizeWithoutIdentityHasRedirectUri()
    {
        $serverRequest = $this->getServerRequest();
        /** @var Server $server */
        $server = $this->registerImplicitGrantHandler($this->getServer());
        $user = null;

        $response = $server->authorize($user, $serverRequest);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertArrayHasKey('location', $response->getHeaders());
    }

    public function testUnsupportedResponseTypeReturnError()
    {
        $serverRequest = new Request(
            [],
            [],
            'http://example.com/',
            'GET',
            'php://memory',
            [],
            [],
            [
                'client_id' => 'test',
                'redirect_uri' => 'http://example.com',
                'response_type' => 'string',
            ]
        );

        $server = $this->registerImplicitGrantHandler($this->getServer());
        $user = $this->getUser();

        $response = $server->authorize($user, $serverRequest);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(
            "{\"code\":400,\"errors\":{\"response_type\":\"Unsupported response type\"}}",
            $response->getBody()->getContents()
        );
    }

    public function testImplicitGrantReturnErrorWithUnregisteredRedirectUri()
    {
        $serverRequest = new Request(
            [],
            [],
            'http://example.com/',
            'GET',
            'php://memory',
            [],
            [],
            [
                'client_id' => 'test',
                'redirect_uri' => 'http://test.com',
                'response_type' => 'token',
            ]
        );

        $server = $this->registerImplicitGrantHandler($this->getServer());
        $response = $server->authorize($this->getUser(), $serverRequest);

        $this->assertInstanceOf(ResponseInterface::class, $response);

        $this->assertEquals(
            "{\"code\":400,\"errors\":{\"redirect_uri\":\"Uri http://test.com can not register for client test\"}}",
            $response->getBody()->getContents()
        );
    }

    public function testImplicitGrantFlowReturnAccessToken()
    {
        $serverRequest = new Request(
            [],
            [],
            'http://example.com/',
            'GET',
            'php://memory',
            [],
            [],
            [
                'client_id' => 'test',
                'redirect_uri' => 'http://example.com',
                'response_type' => 'token',
            ]
        );

        $server = $this->registerImplicitGrantHandler($this->getServer());
        $response = $server->authorize($this->getUser(), $serverRequest);

        $this->assertInstanceOf(ResponseInterface::class, $response);

        $body = $response->getBody()->getContents();
        $this->assertEquals('', $body);

        $this->assertArrayHasKey('location', $response->getHeaders());
        $this->assertStringMatchesFormat(
            'http://example.com?access_token=%s',
            $response->getHeader('location')[0]
        );
    }

    public function testAuthorizationCodeGrantRequestTheCode()
    {
        $serverRequest = new Request(
            [],
            [],
            'http://example.com/',
            'GET',
            'php://memory',
            [],
            [],
            [
                'client_id' => 'test',
                'redirect_uri' => 'http://example.com',
                'response_type' => 'code',
            ]
        );

        $server = $this->registerAuthCodeGrantHandler($this->getServer());
        $response = $server->authorize($this->getUser(), $serverRequest);

        $this->assertInstanceOf(ResponseInterface::class, $response);

        $body = $response->getBody()->getContents();
        $this->assertEquals('', $body);

        $this->assertArrayHasKey('location', $response->getHeaders());
        $this->assertStringMatchesFormat(
            'http://example.com?code=%s',
            $response->getHeader('location')[0]
        );

        $uri = new Uri($response->getHeader('location')[0]);
        $params = [];
        parse_str($uri->getQuery(), $params);
        $code = $params[AuthCodeGrant::AUTHORIZATION_GRANT];

        $this->assertNotEmpty($code);
    }

    public function testRequestWithoutAuthorizationCodeReturnError()
    {
        $serverRequest = new Request(
            [],
            [],
            'http://example.com/',
            'GET',
            'php://memory',
            [],
            [],
            [
                'grant_type' => 'authorization_code',
                'client_id' => 'test',
                'client_secret' => 'secret',
                'redirect_uri' => 'http://example.com',
            ]
        );

        $server = $this->registerAuthCodeGrantHandler($this->getServer());
        $response = $server->authorize($this->getUser(), $serverRequest);

        $this->assertInstanceOf(Response\JsonResponse::class, $response);
        $this->assertEquals(
            '{"code":400,"errors":{"code":"Required parameter \u0027code\u0027 is missing"}}',
            $response->getBody()->getContents()
        );
    }

    public function testRequestWithEmptyAuthorizationCodeReturnError()
    {
        $serverRequest = new Request(
            [],
            [],
            'http://example.com/',
            'GET',
            'php://memory',
            [],
            [],
            [
                'grant_type' => 'authorization_code',
                'code' => '',
                'client_id' => 'test',
                'client_secret' => 'secret',
                'redirect_uri' => 'http://example.com',
            ]
        );

        $server = $this->registerAuthCodeGrantHandler($this->getServer());
        $response = $server->authorize($this->getUser(), $serverRequest);

        $this->assertInstanceOf(Response\JsonResponse::class, $response);
        $this->assertEquals(
            '{"code":400,"errors":{"code":"The provided authorization code cannot be used"}}',
            $response->getBody()->getContents()
        );
    }

    public function testRequestWithExpiredAuthorizationCodeReturnError()
    {
        $serverRequest = new Request(
            [],
            [],
            'http://example.com/',
            'GET',
            'php://memory',
            [],
            [],
            [
                'grant_type' => 'authorization_code',
                'code' => 'expired',
                'client_id' => 'test',
                'client_secret' => 'secret',
                'redirect_uri' => 'http://example.com',
            ]
        );

        $server = $this->registerAuthCodeGrantHandler($this->getServer());
        $response = $server->authorize($this->getUser(), $serverRequest);

        $this->assertInstanceOf(Response\JsonResponse::class, $response);
        $this->assertEquals(
            '{"code":400,"errors":{"code":"The provided authorization code is expired"}}',
            $response->getBody()->getContents()
        );
    }

    public function testRequestWithUsedAuthorizationCodeReturnError()
    {
        $serverRequest = new Request(
            [],
            [],
            'http://example.com/',
            'GET',
            'php://memory',
            [],
            [],
            [
                'grant_type' => 'authorization_code',
                'code' => 'used',
                'client_id' => 'test',
                'client_secret' => 'secret',
                'redirect_uri' => 'myapp://',
            ]
        );

        $server = $this->registerAuthCodeGrantHandler($this->getServer());
        $response = $server->authorize($this->getUser(), $serverRequest);

        $this->assertInstanceOf(Response\JsonResponse::class, $response);
        $this->assertEquals(
            '{"code":400,"errors":{"code":"The provided authorization code is already used"}}',
            $response->getBody()->getContents()
        );
    }

    public function testRequestWithCorrectAuthorizationCodeReturnAccessToken()
    {
        $serverRequest = new Request(
            [],
            [],
            'http://example.com/',
            'GET',
            'php://memory',
            [],
            [],
            [
                'grant_type' => 'authorization_code',
                'code' => 'test',
                'client_id' => 'test',
                'client_secret' => 'secret',
                'redirect_uri' => 'http://example.com',
            ]
        );

        $server = $this->registerAuthCodeGrantHandler($this->getServer());
        $response = $server->authorize($this->getUser(), $serverRequest);

        $body = $response->getBody()->getContents();
        $payload = Json::decode($body, Json::TYPE_ARRAY);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertNotEmpty($body);
        $this->assertJson($body);
        $this->assertArrayHasKey('access_token', $payload);
        $this->assertNotEmpty($payload['access_token']);
        $this->assertArrayHasKey('token_type', $payload);
        $this->assertEquals($payload['token_type'], 'Bearer');
        $this->assertArrayHasKey('expires_in', $payload);
        $this->assertArrayHasKey('expires_on', $payload);
        $this->assertArrayHasKey('refresh_token', $payload);
        $this->assertNotEmpty($payload['refresh_token']);
    }

    public function testRequestWithCustomUriScheme()
    {
        $serverRequest = new Request(
            [],
            [],
            'http://example.com/',
            'GET',
            'php://memory',
            [],
            [],
            [
                'client_id' => 'testapp',
                'response_type' => 'code',
                'redirect_uri' => 'testapp://authorize',
            ]
        );

        $server = $this->registerAuthCodeGrantHandler($this->getServer());
        $response = $server->authorize($this->getUser(), $serverRequest);

        $this->assertInstanceOf(ResponseInterface::class, $response);

        $body = $response->getBody()->getContents();
        $this->assertEquals('', $body);

        $this->assertArrayHasKey('location', $response->getHeaders());
        $this->assertStringMatchesFormat(
            'testapp://authorize?code=%s',
            $response->getHeader('location')[0]
        );

        $uri = (new UriBuilder())
            ->setAllowedSchemes(['testapp' => 0])
            ->build($response->getHeader('location')[0]);

        $params = [];
        parse_str($uri->getQuery(), $params);
        $code = $params[AuthCodeGrant::AUTHORIZATION_GRANT];

        $this->assertNotEmpty($code);
    }
}
