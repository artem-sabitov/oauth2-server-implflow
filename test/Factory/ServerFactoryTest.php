<?php

namespace OAuth2Test\Grant\Implicit\Factory;

use OAuth2\Grant\Implicit\Factory\ServerFactory;
use OAuth2\Grant\Implicit\Options\ServerOptions;
use OAuth2\Grant\Implicit\Provider\ClientProviderInterface;
use OAuth2\Grant\Implicit\Provider\IdentityProviderInterface;
use OAuth2\Grant\Implicit\Server;
use OAuth2\Grant\Implicit\ServerInterface;
use OAuth2\Grant\Implicit\Storage\TokenStorageInterface;
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
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    public function setUp()
    {
        $this->options = $this->createMock(ServerOptions::class);
        $this->identityProvider = $this->createMock(IdentityProviderInterface::class);
        $this->clientProvider = $this->createMock(ClientProviderInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
    }

    public function getContainer()
    {
        $containerStub = $this->createMock(ContainerInterface::class);
        $containerStub->method('get')
            ->withConsecutive(
                [ServerOptions::class],
                [IdentityProviderInterface::class],
                [ClientProviderInterface::class],
                [TokenStorageInterface::class]
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
