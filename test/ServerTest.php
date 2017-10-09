<?php

namespace Oauth2Test\Grant\Implicit;

use OAuth2\Grant\Implicit\Factory\ServerFactory;
use OAuth2\Grant\Implicit\Server;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

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

    public function testCreateServerFromFactory()
    {
        $factory = new ServerFactory();
        $server = $factory->__invoke($this->container);

        $this->assertInstanceOf(Server::class, $server);
    }
}
