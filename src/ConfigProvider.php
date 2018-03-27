<?php

namespace OAuth2;

class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     */
    public function __invoke() : array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    /**
     * Returns the OAuth configuration
     */
    public function getOAuthConfig() : array
    {
        return [
//            'authentication_uri' => 'http://example.com/login',
//            'available_response_types' => [
//                'token',
//                'code',
//            ],
            // Use 'authorization_handler_map' to map a grant type to flow handler. The
            // key is the response_type parameter, the value is the handler to authorization flow.
//            'authorization_handler_map' => [
            // response_type => Fully\Qualified\ClassOrInterfaceName::class
//                'token' => \OAuth2\Handler\ImplicitGrant::class,
//                'code' => \OAuth2\Handler\AuthCodeGrant::class,
//            ],
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies() : array
    {
        return [
            'factories'  => [
                \OAuth2\ServerInterface::class => \OAuth2\Factory\ServerFactory::class,
            ],
        ];
    }
}
