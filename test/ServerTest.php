<?php

namespace Oauth2Test\Grant\Implicit;

use OAuth2\Grant\Implicit\Storage\AccessTokenStorageInterface;
use OAuth2\Grant\Implicit\Storage\ClientStorageInterface;
use OAuth2\Grant\Implicit\Adapter\AdapterInterface;
use OAuth2\Grant\Implicit\Provider\IdentityProviderInterface;
use OAuth2\Grant\Implicit\Server;
use OAuth2\Grant\Implicit\ServerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequest as Request;

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
        $this->identityProvider = $this->createMock(IdentityProviderInterface::class);
        $this->clientStorage = $this->createMock(ClientStorageInterface::class);
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

    public function testAutoCreateServerRequestFromGlobal()
    {
        $this->assertInstanceOf(ServerRequestInterface::class, $this->server->getServerRequest());
    }

    public function testGetAuthorizationAdapterReturnAdapterInterface()
    {
        $this->assertInstanceOf(AdapterInterface::class, $this->server->getAuthorizationAdapter());
    }

    public function testAuthorize()
    {
        $this->server->authorize();
    }
}
