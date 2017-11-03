<?php

namespace OAuth2Test\Grant\Implicit\Assets;

use OAuth2\Grant\Implicit\Client;
use OAuth2\Grant\Implicit\ClientInterface;
use OAuth2\Grant\Implicit\Storage\ClientStorageInterface;

class TestClientStorage implements ClientStorageInterface
{
    const TEST_CLIENT_ID = 'test';

    /**
     * @var null|array
     */
    private $clients;

    /**
     * @var array
     */
    private $clientProperties = [
        'redirect_uri_list' => [
            'http://example.com'
        ],
    ];

    public function __construct()
    {
        $this->clients[self::TEST_CLIENT_ID] = new Client(
            self::TEST_CLIENT_ID,
            $this->clientProperties['redirect_uri_list']
        );
    }

    /**
     * @return ClientInterface|null
     */
    public function getClientById(string $clientId): ClientInterface
    {
        if (isset($this->clients[$clientId]) === false) {
            return null;
        }

        return $this->clients[$clientId];
    }
}