<?php

namespace OAuth2\Factory;

use OAuth2\Provider\ClientProviderInterface;
use OAuth2\Provider\IdentityProviderInterface;
use OAuth2\Server;
use OAuth2\Storage\AccessTokenStorageInterface;
use Psr\Container\ContainerInterface;

class ServerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config')['oauth2'] ?? [];

        return new Server($options, $identityProvider, $clientStorage, $accessStorage);
    }
}
