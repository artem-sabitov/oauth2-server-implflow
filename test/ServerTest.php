<?php

namespace OAuth2Test\Grant\Implicit;

use OAuth2\Grant\Implicit\Storage\AccessTokenStorageInterface;
use OAuth2\Grant\Implicit\Storage\ClientStorageInterface;
use OAuth2\Grant\Implicit\Adapter\AdapterInterface;
use OAuth2\Grant\Implicit\Provider\IdentityProviderInterface;
use OAuth2\Grant\Implicit\Server;
use OAuth2\Grant\Implicit\ServerInterface;
use OAuth2Test\Grant\Implicit\Assets\TestClientStorage;
use OAuth2Test\Grant\Implicit\Assets\TestIdentityProviderWithoutIdentity;
use OAuth2Test\Grant\Implicit\Assets\TestSuccessIdentityProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequest as Request;
use Zend\Diactoros\Uri;
use Zend\Stdlib\Parameters;

class ServerTest extends TestCase
{
    /**
     * @var IdentityProviderInterface
     */
    private $identityProvider;

    /**
     * @var ClientStorageInterface
     */
    private $clientStorage;

    /**
     * @var AccessTokenStorageInterface
     */
    private $accessTokenStorage;

    /**
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * @var ServerInterface|Server
     */
    private $server;

    protected function setUp()
    {
        $this->identityProvider = new TestSuccessIdentityProvider();
        $this->clientStorage = new TestClientStorage();
        $this->accessTokenStorage = $this->createMock(AccessTokenStorageInterface::class);
        $this->request = new Request([], [], 'http://example.com/', 'GET', 'php://memory');

        $this->server = new Server(
            $this->identityProvider,
            $this->clientStorage,
            $this->accessTokenStorage,
            $this->request
        );
    }

    public function testImplementsServerInterface()
    {
        $this->assertInstanceOf(ServerInterface::class, $this->server);
    }

    public function testGetServerRequestReturnServerRequestInterface()
    {
        $this->assertInstanceOf(ServerRequestInterface::class, $this->server->getServerRequest());
    }

    public function testGetAuthorizationAdapterReturnAdapterInterface()
    {
        $this->assertInstanceOf(AdapterInterface::class, $this->server->getAuthorizationAdapter());
    }

    public function testAuthorizeReturnAccessToken()
    {
        $this->request = new Request(
            [], [],
            'http://example.com/', 'GET', 'php://memory',
            [], [], [
                'client_id' => 'test',
                'redirect_uri' => 'http://example.com',
        ]);

        $this->server = new Server(
            $this->identityProvider,
            $this->clientStorage,
            $this->accessTokenStorage,
            $this->request
        );

        $response = $this->server->authorize();
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $this->assertArrayHasKey('location', $response->getHeaders());

        $body = $response->getBody()->getContents();
        $this->assertEquals('', $body);

        $redirectUri = new Uri($response->getHeader('location')[0]);
        $this->assertStringMatchesFormat('access_token=%s', $redirectUri->getQuery());
    }

    public function testAuthorizeWithoutClientId()
    {
        $response = $this->server->authorize();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(
            "{\"code\":400,\"message\":\"You must include a valid \u0027client_id\u0027 parameter\"}",
            $response->getBody()->getContents()
        );
    }

    public function testAuthorizeWithoutIdentityHasRedirectUri()
    {
        $this->identityProvider = new TestIdentityProviderWithoutIdentity();

        $this->server = new Server(
            $this->identityProvider,
            $this->clientStorage,
            $this->accessTokenStorage,
            $this->request
        );

        $response = $this->server->authorize();

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertArrayHasKey('location', $response->getHeaders());
    }
}
