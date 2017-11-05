<?php

namespace OAuth2\Grant\Implicit;

class Client implements ClientInterface
{
    /**
     * @var string
     */
    protected $identificator;

    /**
     * @var string
     */
    protected $redirectUriList;

    /**
     * Client constructor.
     * @param string $identificator
     * @param string|array $redirectUri
     */
    public function __construct(string $identificator, string $redirectUri)
    {
        $this->identificator = $identificator;
        $this->redirectUriList = $redirectUri;
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
        return $this->redirectUriList;
    }
}
