<?php

declare(strict_types=1);

namespace OAuth2\Repository;

use OAuth2\ClientInterface;

interface ClientRepositoryInterface
{
    public function write(ClientInterface $client) : ClientInterface;

    public function find(string $client) : ?ClientInterface;
}
