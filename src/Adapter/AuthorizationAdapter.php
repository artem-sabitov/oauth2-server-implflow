<?php

namespace OAuth2\Grant\Implicit\Adapter;

class AuthorizationAdapter implements AdapterInterface
{
    const CLIENT_ID_KEY = 'client_id';
    const REDIRECT_URI_KEY = 'redirect_uri';
    const RESPONSE_TYPE_KEY = 'response_type';

    /**
     * @var string|null
     */
    protected $clientId;

    /**
     * @var string|null
     */
    protected $redirectUri;

    /**
     * @var string|null
     */
    protected $responseType;

    /**
     * AuthorizationAdapter constructor.
     * @param array $params
     * @throws \InvalidArgumentException
     */
    public function __construct(array $params)
    {
        $this->setParams($params);
    }

    /**
     * @inheritdoc
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId(string $clientId)
    {
        $this->clientId = $clientId;
    }

    /**
     * @inheritdoc
     */
    public function getRedirectUri()
    {
        return $this->redirectUri;
    }

    /**
     * @param string $redirectUri
     */
    public function setRedirectUri(string $redirectUri)
    {
        $this->redirectUri = $redirectUri;
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
     */
    public function setResponseType(string $responseType)
    {
        $this->responseType = $responseType;
    }

    /**
     * @param array $params
     * @return AuthorizationAdapter
     */
    protected function setParams(array $params)
    {
        foreach ($params as $key => $value) {
            switch ($key) {
                case self::CLIENT_ID_KEY:
                    $this->setClientId($value);
                    break;
                case self::REDIRECT_URI_KEY:
                    $this->setRedirectUri($value);
                    break;
                case self::RESPONSE_TYPE_KEY:
                    $this->setResponseType($value);
                    break;
            }
        }

        return $this;
    }
}
