<?php

namespace OAuth2;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'authentication_uri' => 'http://example.com/login',
            'available_response_types' => [
                'token',
                'code',
            ],
            // Use 'authorization_handler_map' to map a grant type to flow handler. The
            // key is the response_type parameter, the value is the handler to authorization flow.
            'authorization_handler_map' => [
                // response_type => Fully\Qualified\ClassOrInterfaceName::class
                'token' => \OAuth2\Handler\ImplicitGrant::class,
                'code' => \OAuth2\Handler\AuthCodeGrant::class,
            ],
        ];
    }
}
