<?php

namespace OAuth2\Grant\Implicit\Storage;

use OAuth2\Grant\Implicit\ClientInterface;

interface ClientStorageInterface
{
    /**
     * @return ClientInterface|null
     */
    public function getClientById(string $clientId): ClientInterface;
}
