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
     * Client constructor.
     * @param string $identificator
     * @param string|array $redirectUri
     */
    public function __construct(string $identificator, string $redirectUri)
    {
        $this->identificator = $identificator;
        $this->redirectUri = $redirectUri;
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
}
