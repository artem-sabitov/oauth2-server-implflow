<?php

declare(strict_types=1);

namespace OAuth2Test;

use OAuth2\Exception;
use OAuth2\Factory\AuthorizationCodeHandlerFactory;
use OAuth2\Factory\ImplicitHandlerFactory;
use OAuth2\Handler\AuthCodeGrant;
use OAuth2\Handler\ImplicitGrant;
use OAuth2\Provider\ClientProviderInterface;
use OAuth2\Repository\AccessTokenRepositoryInterface;
use OAuth2\Repository\AuthorizationCodeRepositoryInterface;
use OAuth2\Repository\RefreshTokenRepositoryInterface;
use OAuth2\TokenRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Container\ContainerInterface;

class AuthorizationCodeHandlerFactoryTest extends TestCase
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
        $this->factory = new AuthorizationCodeHandlerFactory();
        $clientProvider = $this->prophesize(ClientProviderInterface::class);
        $accessTokenRepository = $this->prophesize(AccessTokenRepositoryInterface::class);
        $refreshTokenRepository = $this->prophesize(RefreshTokenRepositoryInterface::class);
        $codeRepository = $this->prophesize(AuthorizationCodeRepositoryInterface::class);

        $this->container
            ->get(ClientProviderInterface::class)
            ->willReturn($clientProvider->reveal());
        $this->container
            ->has(ClientProviderInterface::class)
            ->willReturn(true);
        $this->container
            ->get(AccessTokenRepositoryInterface::class)
            ->willReturn($accessTokenRepository->reveal());
        $this->container
            ->has(AccessTokenRepositoryInterface::class)
            ->willReturn(true);
        $this->container
            ->get(RefreshTokenRepositoryInterface::class)
            ->willReturn($refreshTokenRepository->reveal());
        $this->container
            ->has(RefreshTokenRepositoryInterface::class)
            ->willReturn(true);
        $this->container
            ->get(AuthorizationCodeRepositoryInterface::class)
            ->willReturn($codeRepository->reveal());
        $this->container
            ->has(AuthorizationCodeRepositoryInterface::class)
            ->willReturn(true);
    }

    public function testFactoryWithoutConfig()
    {
        $handler = AuthCodeGrant::class;
        $this->container->get('config')->willReturn([]);
        $this->expectException(Exception\InvalidConfigException::class);
        $this->expectExceptionMessage(sprintf(
            'Cannot create %s handler; config oauth2.authorization_code_flow is missing',
            $handler
        ));
        ($this->factory)($this->container->reveal());
    }

    public function testFactoryWithoutHandlersConfig()
    {
        $handler = AuthCodeGrant::class;
        $this->container->get('config')->willReturn(['oauth2' => []]);
        $this->expectException(Exception\InvalidConfigException::class);
        $this->expectExceptionMessage(sprintf(
            'Cannot create %s handler; config oauth2.authorization_code_flow is missing',
            $handler
        ));
        ($this->factory)($this->container->reveal());
    }

    public function testFactoryWithoutClientProvider()
    {
        $handler = AuthCodeGrant::class;
        $dependency = ClientProviderInterface::class;

        $this->container->get('config')->willReturn([
            'oauth2' => [
                'authorization_code_flow' => [],
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

    public function testFactoryWithoutAccessTokenRepository()
    {
        $this->container->get('config')->willReturn([
            'oauth2' => [
                'authorization_code_flow' => [],
            ],
        ]);
        $this->runTestWithoutDependency(
            AccessTokenRepositoryInterface::class
        );
    }

    public function testFactoryWithoutRefreshTokenRepository()
    {
        $this->container->get('config')->willReturn([
            'oauth2' => [
                'authorization_code_flow' => [],
            ],
        ]);
        $this->runTestWithoutDependency(
            RefreshTokenRepositoryInterface::class
        );
    }

    public function testFactoryWithoutCodeRepository()
    {
        $this->container->get('config')->willReturn([
            'oauth2' => [
                'authorization_code_flow' => [],
            ],
        ]);
        $this->runTestWithoutDependency(
            AuthorizationCodeRepositoryInterface::class
        );
    }

    private function runTestWithoutDependency(string $dependency)
    {
        $this->container->has($dependency)->willReturn(false);
        $this->expectException(Exception\InvalidConfigException::class);
        $this->expectExceptionMessage(sprintf(
            'Cannot create %s handler; dependency %s is missing',
            AuthCodeGrant::class,
            $dependency
        ));
        ($this->factory)($this->container->reveal());
    }

    public function testFactory()
    {
        $this->container->get('config')->willReturn([
            'oauth2' => [
                'authorization_code_flow' => [],
            ],
        ]);
        $server = ($this->factory)($this->container->reveal());
        $this->assertInstanceOf(AuthCodeGrant::class, $server);
    }
}
