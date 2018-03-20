<?php

declare(strict_types=1);

namespace OAuth2\Request;

use InvalidArgumentException;
use OAuth2\Exception\ParameterException;
use Psr\Http\Message\ServerRequestInterface;

class AuthorizationRequest
{
    const CLIENT_ID_KEY = 'client_id';
    const REDIRECT_URI_KEY = 'redirect_uri';
    const RESPONSE_TYPE_KEY = 'response_type';

    /**
     * @var array
     */
    protected $requiredParameters = [
        self::CLIENT_ID_KEY,
        self::REDIRECT_URI_KEY,
        self::RESPONSE_TYPE_KEY,
    ];

    protected $parameters = [];

    /**
     * @var string
     */
    protected $clientId = '';

    /**
     * @var string|null
     */
    protected $redirectUri = '';

    /**
     * @var string|null
     */
    protected $responseType = '';

    /**
     * AuthorizationRequest constructor.
     * @param ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->setParams($request->getQueryParams());
    }

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     * @return AuthorizationRequest
     * @throw InvalidArgumentException
     */
    public function withClientId(string $clientId): AuthorizationRequest
    {
        $new = clone $this;
        $new->clientId = $clientId;

        return $new;
    }

    /**
     * @return string
     */
    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    /**
     * @param string $redirectUri
     * @return AuthorizationRequest
     * @throw InvalidArgumentException
     */
    public function withRedirectUri(string $redirectUri): AuthorizationRequest
    {
        $new = clone $this;
        $new->redirectUri = $redirectUri;

        return $new;
    }

    /**
     * @return string
     */
    public function getResponseType(): string
    {
        return $this->responseType;
    }

    /**
     * @param string $responseType
     * @return AuthorizationRequest
     * @throw InvalidArgumentException
     */
    public function withResponseType(string $responseType): AuthorizationRequest
    {
        $new = clone $this;
        $new->responseType = $responseType;

        return $new;
    }

    /**
     * @param string $key
     * @return string
     */
    public function get(string $key): string
    {
        $value = '';
        if (isset($this->parameters[$key]) === true) {
            $value = $this->parameters[$key];
        }

        return $value;
    }

    /**
     * @param array $params
     */
    private function setParams(array $params): void
    {
        foreach ($params as $key => $value) {
            $this->parameters[$key] = $value;

            switch ($key) {
                case self::CLIENT_ID_KEY:
                    $this->clientId = $value;
                    break;
                case self::REDIRECT_URI_KEY:
                    $this->redirectUri = $value;
                    break;
                case self::RESPONSE_TYPE_KEY:
                    $this->responseType = $value;
                    break;
            }
        }
    }
}
