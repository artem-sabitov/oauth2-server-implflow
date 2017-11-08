<?php

namespace OAuth2Test\Grant\Implicit\Assets;

use InvalidArgumentException;
use OAuth2\Grant\Implicit\AuthorizationRequest;
use OAuth2\Grant\Implicit\Client;
use OAuth2\Grant\Implicit\ClientInterface;
use OAuth2\Grant\Implicit\Exception\ParameterException;
use OAuth2\Grant\Implicit\Provider\ClientProviderInterface;

class TestClientProvider implements ClientProviderInterface
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
    ];

    public function __construct()
    {
        $clientId = $this->clientProperties['identificator'];
        $this->clients[$clientId] = new Client(
            $this->clientProperties['identificator'],
            $this->clientProperties['redirect_uri']
        );
    }

    /**
     * @return ClientInterface
     * @throws InvalidArgumentException
     */
    public function getClientById(string $clientId): ClientInterface
    {
        if (isset($this->clients[$clientId]) === false) {
            throw ParameterException::createInvalidParameter(
                AuthorizationRequest::CLIENT_ID_KEY
            );
        }

        return $this->clients[$clientId];
    }

    public function hasClientById(string $clientId): bool
    {
        return isset($this->clients[$clientId]) &&
            $this->clients[$clientId] instanceof ClientInterface;
    }
}
