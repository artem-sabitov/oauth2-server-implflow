<?php

namespace OAuth2;

class Client implements ClientInterface
{
    /**
     * @var string
     */
    protected $identificator;

    /**
     * @var string
     */
    protected $redirectUri;

    /**
     * @var string
     */
    protected $secret;

    /**
     * Client constructor.
     * @param string $identificator
     * @param string|array $redirectUri
     */
    public function __construct(string $identificator, string $redirectUri, string $secret)
    {
        $this->identificator = $identificator;
        $this->redirectUri = $redirectUri;
        $this->secret = $secret;
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->identificator;
    }

    /**
     * @return array
     */
    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    public function getClient(): string
    {
        return $this->secret;
    }
}
