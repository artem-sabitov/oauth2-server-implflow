<?php

declare(strict_types=1);

namespace OAuth2\Factory;

use OAuth2\Exception;
use OAuth2\Handler\AuthorizationHandlerInterface;
use OAuth2\Server;
use OAuth2\ServerInterface;
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

        if (! isset($config['authorization_handlers'])) {
            throw new Exception\InvalidConfigException(
                'No authorization handlers configured for server'
            );
        }

        $server = new Server($config, $container->get(ResponseInterface::class));
        $this->registerHandlers($container, $server, $config['authorization_handlers']);

        return $server;
    }

    /**
     * @throws Exception\InvalidConfigException
     */
    private function registerHandlers(ContainerInterface $container, ServerInterface $server, array $handlers) : void
    {
        /** @var AuthorizationHandlerInterface $handler */
        foreach ($handlers as $responseType => $handler) {
            if (! $container->has($handler)) {
                throw new Exception\InvalidConfigException(sprintf(
                    '%s handler is missing for for grant type \'%s\'',
                    $handler,
                    $responseType
                ));
            }

            $server->registerHandler($responseType, $container->get($handler));
        }
    }
}
