<?php

namespace OAuth2Test\Grant\Implicit;

use InvalidArgumentException;
use OAuth2\Grant\Implicit\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testConstructorSetArgumentsCorrectly()
    {
        $identificator = 'test_id';
        $redirectUri = 'http://example.com';

        $client = new Client($identificator, $redirectUri);
        $this->assertEquals($identificator, $client->getClientId());
        $this->assertEquals($redirectUri, $client->getRedirectUri());
    }
}
