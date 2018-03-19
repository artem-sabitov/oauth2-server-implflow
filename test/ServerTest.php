<?php

declare(strict_types=1);

namespace OAuth2Test;

use OAuth2\ConfigProvider;
use OAuth2\Handler\AbstractAuthorizationHandler;
use OAuth2\Handler\AuthCodeGrant;
use OAuth2\Handler\ImplicitGrant;
use OAuth2\Options\Options;
use OAuth2\Request\AuthorizationRequest;
use OAuth2\ClientInterface;
use OAuth2\Provider\ClientProviderInterface;
use OAuth2\Provider\IdentityProviderInterface;
use OAuth2\Server;
use OAuth2\ServerInterface;
use OAuth2\Storage\AccessTokenStorageInterface;
use OAuth2\Validator\AuthorizationRequestValidator;
use OAuth2Test\Assets\TestClientProvider;
use OAuth2Test\Assets\TestIdentityProviderWithoutIdentity;
use OAuth2Test\Assets\TestSuccessIdentityProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use ReflectionProperty;
use Zend\Diactoros\ServerRequest as Request;
use Zend\Diactoros\Uri;

class ServerTest extends TestCase
{
    /**
     * @var Options
     */
    protected $serverOptions;

    /**
     * @var IdentityProviderInterface
     */
    private $identityProvider;

    /**
     * @var ClientProviderInterface
     */
    private $clientProvider;

    /**
     * @var AccessTokenStorageInterface
     */
    private $accessTokenStorage;

    /**
     * @var ImplicitGrant
     */
    private $authorizationHandler;

    protected function setUp()
    {
        $config = new ConfigProvider();

        $this->serverOptions = new Options($config());
        $this->identityProvider = new TestSuccessIdentityProvider();
        $this->clientProvider = new TestClientProvider();
        $this->accessTokenStorage = $this->createMock(AccessTokenStorageInterface::class);
        $this->authorizationHandler = new ImplicitGrant(
            $this->serverOptions,
            $this->identityProvider,
            $this->clientProvider,
            $this->accessTokenStorage
        );
    }

    public function getServer()
    {
        return new Server(
            $this->serverOptions,
            $this->identityProvider,
            $this->clientProvider,
            $this->accessTokenStorage
        );
    }

    public function getServerRequest()
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

    public function testConstructorAcceptsAnArguments()
    {
        $server = $this->getServer();

        $r = new ReflectionProperty($server, 'accessTokenStorage');
        $r->setAccessible(true);
        $accessTokenStorage = $r->getValue($server);

        $this->assertInstanceOf(Server::class, $server);
        $this->assertSame($this->identityProvider, $server->getIdentityProvider());
        $this->assertSame($this->clientProvider, $server->getClientProvider());
        $this->assertSame($this->accessTokenStorage, $accessTokenStorage);
    }

    public function testInstanceImplementsServerInterface()
    {
        $this->assertInstanceOf(ServerInterface::class, $this->getServer());
    }

    public function testServerCanCreateAuthorizationRequestFromGlobalServerRequest()
    {
        $_GET = [
            'client_id' => 'test',
            'redirect_uri' => 'http://example.com',
            'response_type' => 'token',
        ];

        $this->assertInstanceOf(
            AuthorizationRequest::class,
            $this->getServer()->getAuthorizationRequest()
        );
    }

    public function testSetAuthorizationRequestReturnNewServerInstanceWithRequest()
    {
        $server = $this->getServer();
        $newServerInstance = $server->setAuthorizationRequest(
            new AuthorizationRequest($this->getServerRequest())
        );

        $this->assertNotSame($server, $newServerInstance);
    }

    public function testAuthorizationWithoutClientIdReturnError()
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

        $server = $this->getServer();
        $response = $server->authorize($serverRequest);

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

        $server = $this->getServer();
        $response = $server->authorize($serverRequest);

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

        $server = $this->getServer();
        $response = $server->authorize($serverRequest);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(
            "{\"code\":400,\"errors\":{\"response_type\":\"Required parameter \u0027response_type\u0027 is missing\"}}",
            $response->getBody()->getContents()
        );
    }

    public function testAuthorizeWithoutIdentityHasRedirectUri()
    {
        $this->identityProvider = new TestIdentityProviderWithoutIdentity();

        $server = $this->getServer();
        $response = $server->authorize($this->getServerRequest());

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertArrayHasKey('location', $response->getHeaders());
    }

    public function testGetClientProviderFromServer()
    {
        $clientProvider = $this->getServer()->getClientProvider();
        $this->assertInstanceOf(ClientProviderInterface::class, $clientProvider);
        $this->assertSame($this->clientProvider, $clientProvider);
    }

    public function testSetClientProviderReturnNewServerInstance()
    {
        $server = $this->getServer();
        $newTestClientProvider = new TestClientProvider();
        $newServer = $server->setClientProvider($newTestClientProvider);

        $this->assertNotSame($server, $newServer);
    }

    public function testGetIdentityProviderFromServer()
    {
        $identityProvider = $this->getServer()->getIdentityProvider();
        $this->assertInstanceOf(IdentityProviderInterface::class, $identityProvider);
        $this->assertSame($this->identityProvider, $identityProvider);
    }

    public function testSetIdentityProviderReturnNewServerInstance()
    {
        $server = $this->getServer();
        $newTestIdentityProvider = new TestSuccessIdentityProvider();
        $newServer = $server->setIdentityProvider($newTestIdentityProvider);

        $this->assertNotSame($server, $newServer);
    }

    public function testGetAuthorizationRequestFromServer()
    {
        $request = $this->getServer()->getAuthorizationRequest();
        $this->assertInstanceOf(AuthorizationRequest::class, $request);
    }

    public function testSetAuthorizationRequestReturnNewServerInstance()
    {
        $server = $this->getServer();

        /** @var AuthorizationRequest $newRequest */
        $newRequest = $this->createMock(AuthorizationRequest::class);
        $request = $server->getAuthorizationRequest();
        $this->assertNotSame($request, $newRequest);

        $newServer = $server->setAuthorizationRequest($newRequest);

        $this->assertNotSame($server, $newServer);
    }

    public function testGetAuthorizationRequestValidatorReturnNewValidator()
    {
        $server = $this->getServer();
        $validator = $server->getAuthorizationRequestValidator();

        $this->assertInstanceOf(AuthorizationRequestValidator::class, $validator);
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

        $server = $this->getServer();
        $response = $server->authorize($serverRequest);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(
            "{\"code\":400,\"errors\":{\"response_type\":\"Unsupported response type\"}}",
            $response->getBody()->getContents()
        );
    }

    public function testImplicitGrantFlowReturnAccessToken()
    {
        $server = $this->getServer();
        $response = $server->authorize($this->getServerRequest());

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
        $server = $this->getServer();

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

        $response = $server->authorize($serverRequest);

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
}
