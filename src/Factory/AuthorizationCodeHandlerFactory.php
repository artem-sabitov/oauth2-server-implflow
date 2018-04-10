<?php

declare(strict_types=1);

namespace OAuth2\Factory;

use OAuth2\Exception;
use OAuth2\Handler\AuthCodeGrant;
use OAuth2\Repository\ClientRepositoryInterface;
use OAuth2\Repository\AccessTokenRepositoryInterface;
use OAuth2\Repository\AuthorizationCodeRepositoryInterface;
use OAuth2\Repository\RefreshTokenRepositoryInterface;
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

        if (! $container->has(ClientRepositoryInterface::class)) {
            throw new Exception\InvalidConfigException(sprintf(
                'Cannot create %s handler; dependency %s is missing',
                AuthCodeGrant::class,ClientRepositoryInterface::class
            ));
        }

        if (! $container->has(AccessTokenRepositoryInterface::class)) {
            throw new Exception\InvalidConfigException(sprintf(
                'Cannot create %s handler; dependency %s is missing',
                AuthCodeGrant::class,AccessTokenRepositoryInterface::class
            ));
        }

        if (! $container->has(RefreshTokenRepositoryInterface::class)) {
            throw new Exception\InvalidConfigException(sprintf(
                'Cannot create %s handler; dependency %s is missing',
                AuthCodeGrant::class,RefreshTokenRepositoryInterface::class
            ));
        }

        if (! $container->has(AuthorizationCodeRepositoryInterface::class)) {
            throw new Exception\InvalidConfigException(sprintf(
                'Cannot create %s handler; dependency %s is missing',
                AuthCodeGrant::class,AuthorizationCodeRepositoryInterface::class
            ));
        }

        return new AuthCodeGrant(
            $config,
            $container->get(ClientRepositoryInterface::class),
            $container->get(AccessTokenRepositoryInterface::class),
            $container->get(RefreshTokenRepositoryInterface::class),
            $container->get(AuthorizationCodeRepositoryInterface::class)
        );
    }
}
