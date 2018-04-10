<?php

declare(strict_types=1);

namespace OAuth2;

use Psr\Http\Message\UriInterface;

class UriBuilder
{
    private $allowedSchemes = [
        'http'  => 80,
        'https' => 443,
    ];

    public function setAllowedSchemes(array $allowedSchemes) : UriBuilder
    {
        $this->allowedSchemes = $allowedSchemes;

        return $this;
    }

    public function build(string $uri = '') : UriInterface
    {
        return new CustomSchemeUri($uri, $this->allowedSchemes);
    }
}
