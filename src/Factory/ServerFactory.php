<?php

namespace OAuth2\Grant\Implicit\Factory;

use OAuth2\Grant\Implicit\Options\ServerOptions;
use OAuth2\Grant\Implicit\Provider\ClientProviderInterface;
use OAuth2\Grant\Implicit\Provider\IdentityProviderInterface;
use OAuth2\Grant\Implicit\Server;
use OAuth2\Grant\Implicit\Storage\TokenStorageInterface;
use Psr\Container\ContainerInterface;

class ServerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $options = $container->get(ServerOptions::class);
        $identityProvider = $container->get(IdentityProviderInterface::class);
        $clientStorage = $container->get(ClientProviderInterface::class);
        $accessStorage = $container->get(TokenStorageInterface::class);

        return new Server($options, $identityProvider, $clientStorage, $accessStorage);
    }
}
