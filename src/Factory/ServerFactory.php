<?php

namespace OAuth2\Grant\Implicit\Factory;

use OAuth2\Grant\Implicit\Server;
use Psr\Container\ContainerInterface;

class ServerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new Server();
    }
}
