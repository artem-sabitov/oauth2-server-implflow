<?php

namespace Oauth2Test\Grant\Implicit;

use OAuth2\Grant\Implicit\Factory\ServerFactory;
use OAuth2\Grant\Implicit\Server;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Request;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Uri;

class ServerTest extends TestCase
{
    /**
     * @var ContainerInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $container;

    /**
     * @var ServerRequestInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    protected function setUp()
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->request = $this->createMock(ServerRequestInterface::class);
    }

    /**
     * @return ServerRequest
     */
    protected function createAuthorizationServerRequest()
    {
        $serverRequest = (ServerRequestFactory::fromGlobals())
            ->withUri(new Uri('http://server/oauth2/authorize'))
            ->withQueryParams([
                'client_id' => 'app',
                'redirect_uri' => 'http://app',
                'response_type' => 'token'
            ]);

        return $serverRequest;
    }

    public function testCreateServerFromFactory()
    {
        $factory = new ServerFactory();
        $server = $factory->__invoke($this->container);

        $this->assertInstanceOf(Server::class, $server);
    }

    public function testServerAuthorizeMethodCanExecute()
    {
        $factory = new ServerFactory();
        $server = $factory->__invoke($this->container);

        $server->authorize($this->createAuthorizationServerRequest());
    }
}
