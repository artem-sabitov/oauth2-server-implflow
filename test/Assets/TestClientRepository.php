<?php

namespace OAuth2Test\Assets;

use OAuth2\Client;
use OAuth2\ClientInterface;
use OAuth2\Repository\ClientRepositoryInterface;

class TestClientRepository implements ClientRepositoryInterface
{
    /**
     * @var null|array
     */
    private $clients;

    public function __construct($clientProperties = [])
    {
        if (! empty($clientProperties)) {
            $this->clientProperties = $clientProperties;
        }
        $this->clients['test'] = new Client('test','http://example.com','secret');
        $this->clients['testapp'] = new Client('test','testapp://authorize','secret');
    }

    public function write(ClientInterface $client): ClientInterface
    {
        return $client;
    }

    public function find(string $client): ?ClientInterface
    {
        if (isset($this->clients[$client]) === false) {
            return null;
        }

        return $this->clients[$client];
    }
}
