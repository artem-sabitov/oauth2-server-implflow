<?php

namespace OAuth2\Grant\Implicit\Storage;

use OAuth2\Grant\Implicit\ClientInterface;

interface ClientStorageInterface
{
    /**
     * @throws \InvalidArgumentException
     * @return ClientInterface
     */
    public function getClientById(string $clientId): ClientInterface;
}
