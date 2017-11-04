<?php

namespace OAuth2\Grant\Implicit;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

class AuthorizationRequest
{
    private const MAX_PARAMETER_LENGTH = 128;

    const CLIENT_ID_KEY = 'client_id';
    const REDIRECT_URI_KEY = 'redirect_uri';
    const RESPONSE_TYPE_KEY = 'response_type';

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
        $this->validateQueryParameter(self::CLIENT_ID_KEY, $clientId);

        $new = clone $this;
        $new->clientId = $clientId;

        return $new;
    }

    /**
     * @inheritdoc
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
        $this->validateQueryParameter(self::REDIRECT_URI_KEY, $redirectUri);
        $new = clone $this;
        $new->redirectUri = $redirectUri;

        return $new;
    }

    /**
     * @inheritdoc
     */
    public function getResponseType()
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
        $this->validateQueryParameter(self::RESPONSE_TYPE_KEY, $responseType);
        $new = clone $this;
        $new->responseType = $responseType;

        return $new;
    }

    /**
     * @param array $params
     */
    private function setParams(array $params): void
    {
        foreach ($params as $key => $value) {
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

    /**
     * @param string $clientId
     * @return bool
     * @throw InvalidArgumentException;
     */
    private function validateQueryParameter(string $name, string $value): void
    {
        if (mb_strlen($value) === 0 || mb_strlen($value) > self::MAX_PARAMETER_LENGTH) {
            throw new InvalidArgumentException(sprintf(
                'Invalid %s %s', $name, $value
            ));
        }
    }
}
