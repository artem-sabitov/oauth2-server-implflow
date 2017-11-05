<?php

namespace OAuth2\Grant\Implicit\Provider;

use OAuth2\Grant\Implicit\ClientInterface;

interface ClientProviderInterface
{
    /**
     * @return ClientInterface
     * @throws \InvalidArgumentException
     */
    public function getClientById(string $clientId): ClientInterface;
}
