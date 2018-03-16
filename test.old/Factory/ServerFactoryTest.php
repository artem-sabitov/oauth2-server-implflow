<?php

namespace OAuth2Test\Factory;

use OAuth2\Factory\ServerFactory;
use OAuth2\Options\ServerOptions;
use OAuth2\Provider\ClientProviderInterface;
use OAuth2\Provider\IdentityProviderInterface;
use OAuth2\Server;
use OAuth2\ServerInterface;
use OAuth2\Storage\AccessTokenStorageInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ReflectionProperty;

class ServerFactoryTest extends TestCase
{
    /**
     * @var ServerOptions
     */
    protected $options;

    /**
     * @var IdentityProviderInterface
     */
    protected $identityProvider;

    /**
     * @var ClientProviderInterface
     */
    protected $clientProvider;

    /**
     * @var AccessTokenStorageInterface
     */
    protected $tokenStorage;

    public function setUp()
    {
        $this->options = $this->createMock(ServerOptions::class);
        $this->identityProvider = $this->createMock(IdentityProviderInterface::class);
        $this->clientProvider = $this->createMock(ClientProviderInterface::class);
        $this->tokenStorage = $this->createMock(AccessTokenStorageInterface::class);
    }

    public function getContainer()
    {
        $containerStub = $this->createMock(ContainerInterface::class);
        $containerStub->method('get')
            ->withConsecutive(
                [ServerOptions::class],
                [IdentityProviderInterface::class],
                [ClientProviderInterface::class],
                [AccessTokenStorageInterface::class]
            )
            ->willReturnOnConsecutiveCalls(
                $this->options,
                $this->identityProvider,
                $this->clientProvider,
                $this->tokenStorage
            );

        return $containerStub;
    }

    public function getPrivateFieldFromServer(Server $server, string $field)
    {
        $r = new ReflectionProperty($server, $field);
        $r->setAccessible(true);
        return $r->getValue($server);
    }

    public function testFactoryReturnsServerInstance()
    {
        $container = $this->getContainer();
        $server = (new ServerFactory)->__invoke($container);

        $this->assertInstanceOf(Server::class, $server);
    }

    public function testServerImplementsServerInterface()
    {
        $container = $this->getContainer();
        $server = (new ServerFactory)->__invoke($container);

        $this->assertInstanceOf(ServerInterface::class, $server);
    }

    public function testServerRetrievesSameInstanceInjection()
    {
        $container = $this->getContainer();
        $server = (new ServerFactory)->__invoke($container);

        $options = $this->getPrivateFieldFromServer($server, 'options');
        $this->assertSame($this->options, $options);
        $identityProvider = $this->getPrivateFieldFromServer($server, 'identityProvider');
        $this->assertSame($this->identityProvider, $identityProvider);
        $clientProvider = $this->getPrivateFieldFromServer($server, 'clientProvider');
        $this->assertSame($this->clientProvider, $clientProvider);
        $tokenStorage = $this->getPrivateFieldFromServer($server, 'tokenStorage');
        $this->assertSame($this->tokenStorage, $tokenStorage);
    }
}
