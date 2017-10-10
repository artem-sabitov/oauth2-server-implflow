<?php

use OAuth2\Grant\Implicit\Parameter\ClientIdInterface;

interface ClientStorageInterface
{
    /**
     * @return ClientIdInterface
     * @throws \InvalidArgumentException
     */
    public function getClient(string $clientId): ClientIdInterface;

    /**
     * @param string|null $clientId
     * @param string|null $clientSecret
     * @return ClientIdInterface
     */
    public function createClient(string $clientId = null, string $clientSecret = null): ClientIdInterface;
}