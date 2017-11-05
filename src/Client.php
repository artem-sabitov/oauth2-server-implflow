<?php

namespace OAuth2\Grant\Implicit;

use InvalidArgumentException;

class Client implements ClientInterface
{
    /**
     * @var string
     */
    protected $identificator;

    /**
     * @var array
     */
    protected $redirectUriList = [];

    /**
     * Client constructor.
     * @param string $identificator
     * @param string|array $redirectUri
     */
    public function __construct(string $identificator, $redirectUri)
    {
        if (is_string($redirectUri) === true) {
            $redirectUri = [$redirectUri];
        }

        if (is_array($redirectUri) === false) {
            throw new InvalidArgumentException('The redirect url list must be type string or array');
        }

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
    public function getListRedirectUri(): array
    {
        return $this->redirectUriList;
    }
}
