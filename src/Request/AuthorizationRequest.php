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
    const STATE_KEY = 'state';

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * @var string
     */
    protected $clientId = '';

    /**
     * @var string
     */
    protected $redirectUri = '';

    /**
     * @var string
     */
    protected $responseType = '';

    /**
     * AuthorizationRequest constructor.
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->setParams($request->getQueryParams());
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @throw InvalidArgumentException
     */
    public function withClientId(string $clientId): AuthorizationRequest
    {
        $new = clone $this;
        $new->clientId = $clientId;

        return $new;
    }

    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    /**
     * @throw InvalidArgumentException
     */
    public function withRedirectUri(string $redirectUri): AuthorizationRequest
    {
        $new = clone $this;
        $new->redirectUri = $redirectUri;

        return $new;
    }

    public function getResponseType(): string
    {
        return $this->responseType;
    }

    /**
     * @throw InvalidArgumentException
     */
    public function withResponseType(string $responseType): AuthorizationRequest
    {
        $new = clone $this;
        $new->responseType = $responseType;

        return $new;
    }

    public function get(string $key): ?string
    {
        $value = null;
        if (isset($this->parameters[$key]) === true) {
            $value = $this->parameters[$key];
        }

        return $value;
    }

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
                case self::STATE_KEY:
                    $this->state = $value;
                    break;
            }
        }
    }
}
