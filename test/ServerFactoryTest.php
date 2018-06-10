<?php

declare(strict_types=1);

namespace OAuth2Test;

use OAuth2\Exception;
use OAuth2\Factory\ServerFactory;
use OAuth2\Handler\AuthorizationHandlerInterface;
use OAuth2\ServerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class ServerFactoryTest extends TestCase
{
    /**
     * @var ContainerInterface|ObjectProphecy
     */
    private $container;

    /**
     * @var ServerFactory
     */
    private $factory;

    /**
     * @var ResponseInterface|ObjectProphecy
     */
    private $responsePrototype;

    /**
     * @var callable
     */
    private $responseFactory;

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new ServerFactory();
        $this->responsePrototype = $this->prophesize(ResponseInterface::class);
        $this->responseFactory = function () {
            return $this->responsePrototype->reveal();
        };
        $this->container
            ->get(ResponseInterface::class)
            ->willReturn($this->responseFactory);
    }

    public function testFactoryWithoutConfig()
    {
        $this->container->get('config')->willReturn([]);
        $this->expectException(Exception\InvalidConfigException::class);
        $this->expectExceptionMessage('No authorization config provided');
        ($this->factory)($this->container->reveal());
    }

    public function testFactoryWithoutHandlersConfig()
    {
        $this->container->get('config')->willReturn(['oauth2' => []]);
        $this->expectException(Exception\InvalidConfigException::class);
        $this->expectExceptionMessage('No authorization handlers configured for server');
        ($this->factory)($this->container->reveal());
    }

    public function testFactoryWithInvalidHandlers()
    {
        $responseType = 'test';
        $handler = AuthorizationHandlerInterface::class;

        $this->container->get('config')->willReturn([
            'oauth2' => [
                'authorization_handlers' => [
                    $responseType => $handler,
                ],
            ],
        ]);
        $this->container->has(AuthorizationHandlerInterface::class)->willReturn(false);
        $this->expectException(Exception\InvalidConfigException::class);
        $this->expectExceptionMessage(sprintf(
            '%s handler is missing for for grant type \'%s\'',
            $handler,
            $responseType
        ));
        ($this->factory)($this->container->reveal());
    }

    public function testFactory()
    {
        $this->container->get('config')->willReturn([
            'oauth2' => [
                'authorization_handlers' => [],
            ],
        ]);
        $server = ($this->factory)($this->container->reveal());
        $this->assertInstanceOf(ServerInterface::class, $server);
    }
}
