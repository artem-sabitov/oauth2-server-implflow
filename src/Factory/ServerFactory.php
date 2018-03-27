<?php

namespace OAuth2\Factory;

use OAuth2\Exception;
use OAuth2\Server;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class ServerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config')['oauth2'] ?? null;
        if (null === $config) {
            throw new Exception\InvalidConfigException(
                'No authorization config provided'
            );
        }

        return new Server($config, $container->get(ResponseInterface::class));
    }
}
