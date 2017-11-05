<?php

namespace OAuth2\Grant\Implicit\Factory;

use OAuth2\Grant\Implicit\ConfigProvider;
use OAuth2\Grant\Implicit\Options\ServerOptions;
use Psr\Container\ContainerInterface;

class ServerOptionsFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');

        if ($config === null) {
            $config = (new ConfigProvider())->__invoke();
        }

        if (isset($config['oauth2_server']['implicit_grant']) === true) {
            $config = $config['oauth2_server']['implicit_grant'];
        } else {
            $config = [];
        }

        return new ServerOptions($config);
    }
}
