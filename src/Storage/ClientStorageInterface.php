<?php

namespace OAuth2\Grant\Implicit\Storage;

use OAuth2\Grant\Implicit\ClientInterface;

interface ClientStorageInterface
{
    /**
     * @return ClientInterface
     * @throws \InvalidArgumentException
     */
    public function getClientById(string $clientId): ClientInterface;
}
