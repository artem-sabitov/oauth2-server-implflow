<?php

namespace OAuth2Test\Grant\Implicit;

use OAuth2\Grant\Implicit\AuthorizationRequest;
use OAuth2\Grant\Implicit\ClientInterface;
use OAuth2\Grant\Implicit\Messages;
use OAuth2\Grant\Implicit\Options\ServerOptions;
use OAuth2\Grant\Implicit\Provider\ClientProviderInterface;
use OAuth2\Grant\Implicit\Provider\IdentityProviderInterface;
use OAuth2\Grant\Implicit\Server;
use OAuth2\Grant\Implicit\ServerInterface;
use OAuth2\Grant\Implicit\Storage\TokenStorageInterface;
use OAuth2Test\Grant\Implicit\Assets\TestClientProvider;
use OAuth2Test\Grant\Implicit\Assets\TestIdentityProviderWithoutIdentity;
use OAuth2Test\Grant\Implicit\Assets\TestSuccessIdentityProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequest as Request;

class ServerTest extends TestCase
{
    /**
     * @var ServerOptions
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
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var ServerRequestInterface
     */
    private $serverRequest;

    /**
     * @var ServerInterface|Server
     */
    private $server;

    protected function setUp()
    {
        $this->serverOptions = new ServerOptions();
        $this->identityProvider = new TestSuccessIdentityProvider();
        $this->clientProvider = new TestClientProvider();
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
    }

    public function getServer()
    {
        return new Server(
            $this->serverOptions,
            $this->identityProvider,
            $this->clientProvider,
            $this->tokenStorage
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
        $this->assertInstanceOf(Server::class, $server);
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

    public function testSuccessAuthorizationReturnAccessToken()
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
            "{\"code\":400,\"message\":[\"Required parameter \u0027client_id\u0027 missing\"]}",
            $response->getBody()->getContents()
        );
    }

    public function testAuthorizationWithUndefinedClientIdReturnError()
    {
        $serverRequest = $this->getServerRequest()->withQueryParams([
            'client_id' => 'super_test', // test expected
            'redirect_uri' => 'http://example.com',
            'response_type' => 'token',
        ]);

        $response = $this->getServer()->authorize($serverRequest);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(
            "{\"code\":400,\"message\":[\"Invalid \u0027client_id\u0027 parameter\"]}",
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
            "{\"code\":400,\"message\":[\"Required parameter \u0027redirect_uri\u0027 missing\"]}",
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
            "{\"code\":400,\"message\":[\"Required parameter \u0027response_type\u0027 missing\"]}",
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

    public function testServerClientProviderProvideClientByClientId()
    {
        $clientProvider = $this->getServer()->getClientProvider();
        $client = $clientProvider->getClientById('test');
        $this->assertSame($this->clientProvider, $clientProvider);
        $this->assertInstanceOf(ClientInterface::class, $client);
    }

    public function testGetMessages()
    {
        $messages = $this->getServer()->getMessages();
        $this->assertInstanceOf(Messages::class, $messages);
    }
}
