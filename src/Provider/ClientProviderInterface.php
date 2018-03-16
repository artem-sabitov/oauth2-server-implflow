<?php

namespace OAuth2\Provider;

use OAuth2\ClientInterface;

interface ClientProviderInterface
{
    /**
     * @return ClientInterface
     * @throws \InvalidArgumentException
     */
    public function getClientById(string $clientId): ClientInterface;

    /**
     * @return boolean
     */
    public function hasClientById(string $clientId): bool;
}
