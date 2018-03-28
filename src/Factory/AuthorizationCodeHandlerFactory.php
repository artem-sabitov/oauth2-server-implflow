<?php

declare(strict_types=1);

namespace OAuth2\Factory;

use OAuth2\Exception;
use OAuth2\Handler\AuthCodeGrant;
use OAuth2\Provider\ClientProviderInterface;
use OAuth2\TokenRepositoryInterface;
use Psr\Container\ContainerInterface;

class AuthorizationCodeHandlerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config')['oauth2']['authorization_code_flow'] ?? null;
        if (null === $config) {
            throw new Exception\InvalidConfigException(sprintf(
                'Cannot create %s handler; config oauth2.authorization_code_flow is missing',
                AuthCodeGrant::class
            ));
        }

        if (! $container->has(ClientProviderInterface::class)) {
            throw new Exception\InvalidConfigException(sprintf(
                'Cannot create %s handler; dependency %s is missing',
                AuthCodeGrant::class,ClientProviderInterface::class
            ));
        }

        if (! $container->has(TokenRepositoryInterface::class)) {
            throw new Exception\InvalidConfigException(sprintf(
                'Cannot create %s handler; dependency %s is missing',
                AuthCodeGrant::class,TokenRepositoryInterface::class
            ));
        }

        return new AuthCodeGrant(
            $config,
            $container->get(ClientProviderInterface::class),
            $container->get(TokenRepositoryInterface::class)
        );
    }
}
