<?php

declare(strict_types=1);

namespace OAuth2Test;

use OAuth2\Exception;
use OAuth2\Factory\ImplicitHandlerFactory;
use OAuth2\Handler\ImplicitGrant;
use OAuth2\Repository\AccessTokenRepositoryInterface;
use OAuth2\Repository\ClientRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;

class ImplicitHandlerFactoryTest extends TestCase
{
    /**
     * @var ContainerInterface|ObjectProphecy
     */
    private $container;

    /**
     * @var ImplicitHandlerFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->factory = new ImplicitHandlerFactory();
        $clientRepository = $this->prophesize(ClientRepositoryInterface::class);
        $tokenRepository = $this->prophesize(AccessTokenRepositoryInterface::class);

        $this->container
            ->get(ClientRepositoryInterface::class)
            ->willReturn($clientRepository);
        $this->container
            ->has(ClientRepositoryInterface::class)
            ->willReturn(true);
        $this->container
            ->get(AccessTokenRepositoryInterface::class)
            ->willReturn($tokenRepository->reveal());
        $this->container
            ->has(AccessTokenRepositoryInterface::class)
            ->willReturn(true);
    }

    public function testFactoryWithoutConfig()
    {
        $this->container->get('config')->willReturn([]);
        $this->expectException(Exception\InvalidConfigException::class);
        $this->expectExceptionMessage('Cannot create OAuth2\Handler\ImplicitGrant handler; config oauth2.implicit_grant is missing');
        ($this->factory)($this->container->reveal());
    }

    public function testFactoryWithoutHandlersConfig()
    {
        $this->container->get('config')->willReturn(['oauth2' => []]);
        $this->expectException(Exception\InvalidConfigException::class);
        $this->expectExceptionMessage('Cannot create OAuth2\Handler\ImplicitGrant handler; config oauth2.implicit_grant is missing');
        ($this->factory)($this->container->reveal());
    }

    public function testFactoryWithoutClientProvider()
    {
        $handler = ImplicitGrant::class;
        $dependency = ClientRepositoryInterface::class;

        $this->container->get('config')->willReturn([
            'oauth2' => [
                'implicit_grant' => [],
            ],
        ]);
        $this->container->has($dependency)->willReturn(false);
        $this->expectException(Exception\InvalidConfigException::class);
        $this->expectExceptionMessage(sprintf(
            'Cannot create %s handler; dependency %s is missing',
            $handler,
            $dependency
        ));
        ($this->factory)($this->container->reveal());
    }

    public function testFactoryWithoutTokenRepository()
    {
        $handler = ImplicitGrant::class;
        $dependency = AccessTokenRepositoryInterface::class;

        $this->container->get('config')->willReturn([
            'oauth2' => [
                'implicit_grant' => [],
            ],
        ]);
        $this->container->has($dependency)->willReturn(false);
        $this->expectException(Exception\InvalidConfigException::class);
        $this->expectExceptionMessage(sprintf(
            'Cannot create %s handler; dependency %s is missing',
            $handler,
            $dependency
        ));
        ($this->factory)($this->container->reveal());
    }

    public function testFactory()
    {
        $this->container->get('config')->willReturn([
            'oauth2' => [
                'implicit_grant' => [],
            ],
        ]);
        $server = ($this->factory)($this->container->reveal());
        $this->assertInstanceOf(ImplicitGrant::class, $server);
    }
}
