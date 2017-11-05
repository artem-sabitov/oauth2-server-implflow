<?php

namespace OAuth2\Grant\Implicit;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'oauth2_server' => [
                'implicit_grant' => $this->getImplicitGrantConfig(),
            ]
        ];
    }

    /**
     * @return array
     */
    private function getImplicitGrantConfig(): array
    {
        return [
            'authentication_uri' => 'http://example.com/login',
            'available_response_type' => 'token',
        ];
    }
}
