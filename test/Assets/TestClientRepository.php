<?php

namespace OAuth2Test\Assets;

use InvalidArgumentException;
use OAuth2\Client;
use OAuth2\ClientInterface;
use OAuth2\Exception\ParameterException;
use OAuth2\Repository\ClientRepositoryInterface;
use OAuth2\Request\AuthorizationRequest;

class TestClientRepository implements ClientRepositoryInterface
{
    /**
     * @var null|array
     */
    private $clients;

    /**
     * @var array
     */
    private $clientProperties = [
        'identificator' => 'test',
        'redirect_uri' => 'http://example.com',
        'secret' => 'secret',
    ];

    public function __construct($clientProperties = [])
    {
        if (! empty($clientProperties)) {
            $this->clientProperties = $clientProperties;
        }

        $clientId = $this->clientProperties['identificator'];
        $this->clients[$clientId] = new Client(
            $this->clientProperties['identificator'],
            $this->clientProperties['redirect_uri'],
            $this->clientProperties['secret']
        );
    }

    public function write(ClientInterface $client): void
    {
        // TODO: Implement write() method.
    }

    public function find(string $client): ?ClientInterface
    {
        if (isset($this->clients[$client]) === false) {
            return null;
        }

        return $this->clients[$client];
    }
}
