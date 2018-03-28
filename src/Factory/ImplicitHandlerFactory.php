<?php

declare(strict_types=1);

namespace OAuth2\Factory;

use OAuth2\Exception;
use OAuth2\Handler\ImplicitGrant;
use OAuth2\Provider\ClientProviderInterface;
use OAuth2\TokenRepositoryInterface;
use Psr\Container\ContainerInterface;

class ImplicitHandlerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config')['oauth2']['implicit_flow'] ?? null;
        if (null === $config) {
            throw new Exception\InvalidConfigException(sprintf(
                'Cannot create %s handler; config oauth2.implicit_flow is missing',
                ImplicitGrant::class
            ));
        }

        if (! $container->has(ClientProviderInterface::class)) {
            throw new Exception\InvalidConfigException(sprintf(
                'Cannot create %s handler; dependency %s is missing',
                ImplicitGrant::class,ClientProviderInterface::class
            ));
        }

        if (! $container->has(TokenRepositoryInterface::class)) {
            throw new Exception\InvalidConfigException(sprintf(
                'Cannot create %s handler; dependency %s is missing',
                ImplicitGrant::class,TokenRepositoryInterface::class
            ));
        }

        return new ImplicitGrant(
            $config,
            $container->get(ClientProviderInterface::class),
            $container->get(TokenRepositoryInterface::class)
        );
    }
}
