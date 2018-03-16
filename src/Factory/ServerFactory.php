<?php

namespace OAuth2\Factory;

use OAuth2\Options\ServerOptions;
use OAuth2\Provider\ClientProviderInterface;
use OAuth2\Provider\IdentityProviderInterface;
use OAuth2\Server;
use OAuth2\Storage\AccessTokenStorageInterface;
use Psr\Container\ContainerInterface;

class ServerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $options = $container->get(ServerOptions::class);
        $identityProvider = $container->get(IdentityProviderInterface::class);
        $clientStorage = $container->get(ClientProviderInterface::class);
        $accessStorage = $container->get(AccessTokenStorageInterface::class);

        return new Server($options, $identityProvider, $clientStorage, $accessStorage);
    }
}
