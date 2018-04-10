<?php

declare(strict_types=1);

namespace OAuth2\Factory;

use OAuth2\Exception;
use OAuth2\Handler\AuthorizationCodeGrant;
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
                AuthorizationCodeGrant::class
            ));
        }

        if (! $container->has(ClientRepositoryInterface::class)) {
            throw new Exception\InvalidConfigException(sprintf(
                'Cannot create %s handler; dependency %s is missing',
                AuthorizationCodeGrant::class,ClientRepositoryInterface::class
            ));
        }

        if (! $container->has(AccessTokenRepositoryInterface::class)) {
            throw new Exception\InvalidConfigException(sprintf(
                'Cannot create %s handler; dependency %s is missing',
                AuthorizationCodeGrant::class,AccessTokenRepositoryInterface::class
            ));
        }

        if (! $container->has(RefreshTokenRepositoryInterface::class)) {
            throw new Exception\InvalidConfigException(sprintf(
                'Cannot create %s handler; dependency %s is missing',
                AuthorizationCodeGrant::class,RefreshTokenRepositoryInterface::class
            ));
        }

        if (! $container->has(AuthorizationCodeRepositoryInterface::class)) {
            throw new Exception\InvalidConfigException(sprintf(
                'Cannot create %s handler; dependency %s is missing',
                AuthorizationCodeGrant::class,AuthorizationCodeRepositoryInterface::class
            ));
        }

        return new AuthorizationCodeGrant(
            $config,
            $container->get(ClientRepositoryInterface::class),
            $container->get(AccessTokenRepositoryInterface::class),
            $container->get(RefreshTokenRepositoryInterface::class),
            $container->get(AuthorizationCodeRepositoryInterface::class)
        );
    }
}
