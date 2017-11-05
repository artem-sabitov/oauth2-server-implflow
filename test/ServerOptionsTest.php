<?php

namespace OAuth2Test\Grant\Implicit;

use OAuth2\Grant\Implicit\Options\ServerOptions;
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
        $this->assertEquals(ServerOptions::DEFAULT_RESPONSE_TYPE, $options->getSupportedResponseType());
    }
}
