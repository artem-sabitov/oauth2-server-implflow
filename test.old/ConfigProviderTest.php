<?php

namespace OAuth2Test;

use OAuth2\ConfigProvider;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    public function testInvokeMethodReturnArray()
    {
        $config = (new ConfigProvider())->__invoke();
        $test = [
            'oauth2_server' => [
                'implicit_grant' => [
                ]
            ]
        ];

        $this->assertInternalType('array', $config);
        $this->assertArraySubset($test, $config);
        $config = $config['oauth2_server']['implicit_grant'];
        $this->assertArrayHasKey('authentication_uri', $config);
        $this->assertArrayHasKey('available_response_type', $config);
    }
}
