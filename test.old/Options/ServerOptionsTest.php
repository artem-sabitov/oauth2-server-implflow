<?php

namespace OAuth2Test\Options;

use OAuth2\Options\ServerOptions;
use PHPUnit\Framework\TestCase;

class ServerOptionsTest extends TestCase
{
    public function getOptions()
    {
        return new ServerOptions();
    }

    public function testServerOptionsReturnDefaultResponseType()
    {
        $options = $this->getOptions();
        $this->assertEquals(ServerOptions::RESPONSE_TYPE, $options->getSupportedResponseType());
    }

    public function testServerOptionsReturnDefaultAccessTokenQueryKey()
    {
        $options = $this->getOptions();
        $this->assertEquals(ServerOptions::ACCESS_TOKEN_KEY, $options->getAccessTokenQueryKey());
    }

    public function testServerOptionsSetCorrectFiledValues()
    {
        $options = new ServerOptions([
            'authentication_uri' => 'http://example.com',
            'access_token_query_key' => 'auth_t',
            'supported_response_type' => 'test_token'
        ]);

        $this->assertEquals('http://example.com', $options->getAuthenticationUri());
        $this->assertEquals('auth_t', $options->getAccessTokenQueryKey());
        $this->assertEquals('test_token', $options->getSupportedResponseType());
    }
}
