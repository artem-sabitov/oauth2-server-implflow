<?php

namespace OAuth2\Grant\Implicit\Factory;

use AccessTokenStorageInterface;
use ClientStorageInterface;
use OAuth2\Grant\Implicit\Provider\IdentityProviderInterface;
use OAuth2\Grant\Implicit\Server;
use Psr\Container\ContainerInterface;

class ServerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $identityProvider = $container->get(IdentityProviderInterface::class);
        $clientStorage = $container->get(ClientStorageInterface::class);
        $accessStorage = $container->get(AccessTokenStorageInterface::class);

        return new Server();
    }
}
