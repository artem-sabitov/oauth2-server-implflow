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
            'oauth2' => $this->getOAuth2Config(),
        ];
    }

    /**
     * Returns the OAuth configuration
     */
    public function getOAuth2Config() : array
    {
        return [
            'authentication_uri' => '',
            'authorization_handlers' => [],
            'implicit_flow' => [
                'expiration_time' => 60 * 60 * 6, // 6 hours
                'issuer_identifier' => '',
                'allowed_schemes' => [
                    'https' => 443,
                ],
            ],
            'authorization_code_flow' => [
                'expiration_time' => 60 * 60 * 6, // 6 hours
                'issuer_identifier' => '',
                'refresh_token_extra_time' => 60 * 60 * 720, // 30 days
                'allowed_schemes' => [
                    'https' => 443,
                ],
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
