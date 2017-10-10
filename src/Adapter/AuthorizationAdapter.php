<?php

namespace OAuth2\Grant\Implicit\Adapter;

use OAuth2\Grant\Implicit\Parameter\ClientIdInterface;
use OAuth2\Grant\Implicit\Parameter\RedirectUriInterface;
use OAuth2\Grant\Implicit\Parameter\ResponseTypeInterface;
use PHPUnit\Runner\Exception;
use Psr\Http\Message\ServerRequestInterface;
use Traversable;

class AuthorizationAdapter implements AdapterInterface
{
    const CLIENT_ID_KEY = 'client_id';
    const REDIRECT_URI_KEY = 'redirect_uri';
    const RESPONSE_TYPE_KEY = 'response_type';

    /**
     * @var ClientIdInterface|null
     */
    protected $clientId;

    /**
     * @var RedirectUriInterface|null
     */
    protected $redirectUri;

    /**
     * @var ResponseTypeInterface|null
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
     * @param null|ClientIdInterface $clientId
     */
    public function setClientId($clientId)
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
     * @param null|RedirectUriInterface $redirectUri
     */
    public function setRedirectUri($redirectUri)
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
     * @param null|ResponseTypeInterface $responseType
     */
    public function setResponseType($responseType)
    {
        $this->responseType = $responseType;
    }

    /**
     * @param  array|Traversable $params
     * @throws \InvalidArgumentException
     * @return AuthorizationAdapter
     */
    protected function setParams($params)
    {
        if (is_array($params) === false && $params instanceof Traversable === false) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Parameter provided to %s must be an %s or %s',
                    __METHOD__,
                    'array',
                    'Traversable'
                )
            );
        }

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
