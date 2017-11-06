<?php

namespace OAuth2\Grant\Implicit\Validator;

use InvalidArgumentException;
use OAuth2\Grant\Implicit\AuthorizationRequest;
use OAuth2\Grant\Implicit\Exception\ParameterException;
use OAuth2\Grant\Implicit\Messages;
use OAuth2\Grant\Implicit\Provider\ClientProviderInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthorizationRequestValidator
{
    const INVALID_PARAMETER = 1;
    const MISSING_PARAMETER = 2;

    /**
     * @var string
     */
    protected static $messageTemplates = [
        self::INVALID_PARAMETER => 'Invalid \'%s\' parameter',
        self::MISSING_PARAMETER => 'Required parameter \'%s\' missing',
    ];

    /**
     * @var ClientProviderInterface
     */
    private $clientProvider;

    /**
     * @var string
     */
    private $supportedResponseType;

    /**
     * @var string
     */
    private $supportedRedirectUri;

    /**
     * @var Messages
     */
    private $messages;

    /**
     * AuthorizationRequestValidator constructor.
     * @param string $supportedResponseType
     */
    public function __construct(
        ClientProviderInterface $clientProvider,
        string $supportedResponseType,
        string $supportedRedirectUri
    ) {
        $this->messages = new Messages();
        $this->clientProvider = $clientProvider;
        $this->supportedResponseType = $supportedResponseType;
        $this->supportedRedirectUri = $supportedRedirectUri;
    }

    /**
     * @throws ParameterException
     */
    public function validate($request)
    {
        if ($request instanceof ServerRequestInterface) {
            $request = new AuthorizationRequest($request);
        }

        $this->validateClientId($request->getClientId());
        $this->validateRedirectUri($request->getRedirectUri());
        $this->validateResponseType($request->getResponseType());
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages->toArray();
    }

    /**
     * @return bool
     */
    public function validateClientId(string $clientId): bool
    {
        try {
            $client = $this->clientProvider->getClientById($clientId);
        } catch (InvalidArgumentException $e) {
            $this->messages->addErrorMessage(
                AuthorizationRequest::CLIENT_ID_KEY,
                sprintf(
                    self::$messageTemplates[self::INVALID_PARAMETER],
                    AuthorizationRequest::CLIENT_ID_KEY
                )
            );

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function validateRedirectUri(string $redirectUri): bool
    {
        if ($redirectUri !== $this->supportedRedirectUri) {
            $this->messages->addErrorMessage(
                AuthorizationRequest::RESPONSE_TYPE_KEY,
                sprintf(
                    self::$messageTemplates[self::INVALID_PARAMETER],
                    AuthorizationRequest::RESPONSE_TYPE_KEY
                )
            );
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function validateResponseType(string $responseType): bool
    {
        if ($responseType !== $this->supportedResponseType) {
            $this->messages->addErrorMessage(
                AuthorizationRequest::RESPONSE_TYPE_KEY,
                sprintf(
                    self::$messageTemplates[self::INVALID_PARAMETER],
                    AuthorizationRequest::RESPONSE_TYPE_KEY
                )
            );

            return false;
        }

        return true;
    }
}
