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
            'authentication_uri' => '',
            'authorization_handlers' => [],
            'implicit_flow' => [
                'expiration_time' => 60 * 60 * 6, // 6 hours
                'issuer_identifier' => ''
            ],
            'authorization_code_flow' => [
                'expiration_time' => 60 * 60 * 6, // 6 hours
                'issuer_identifier' => '',
                'refresh_token_extra_time' => 60 * 60,
            ],
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
