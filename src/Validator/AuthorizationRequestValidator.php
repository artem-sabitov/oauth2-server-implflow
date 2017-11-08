<?php

namespace OAuth2\Grant\Implicit\Validator;

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
     * @var Messages
     */
    private $messages;

    /**
     * AuthorizationRequestValidator constructor.
     * @param string $supportedResponseType
     */
    public function __construct(
        ClientProviderInterface $clientProvider,
        string $supportedResponseType
    ) {
        $this->messages = new Messages();
        $this->clientProvider = $clientProvider;
        $this->supportedResponseType = $supportedResponseType;
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
        $this->validateRedirectUri($request->getClientId(), $request->getRedirectUri());
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
        if ($this->clientProvider->hasClientById($clientId) === false) {
            $this->addErrorMessage(
                AuthorizationRequest::CLIENT_ID_KEY,
                self::INVALID_PARAMETER
            );

            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function validateRedirectUri(string $clientId, string $redirectUri): bool
    {
        // Request without a valid client_id
        if ($this->validateClientId($clientId) === false) {
            return false;
        }

        $client = $this->clientProvider->getClientById($clientId);

        if ($redirectUri !== $client->getRedirectUri()) {
            $this->addErrorMessage(
                AuthorizationRequest::REDIRECT_URI_KEY,
                self::INVALID_PARAMETER
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
            $this->addErrorMessage(
                AuthorizationRequest::RESPONSE_TYPE_KEY,
                self::INVALID_PARAMETER
            );

            return false;
        }

        return true;
    }

    /**
     * @param string $key
     * @param int $errorCode
     * @return void
     */
    protected function addErrorMessage(string $key, int $errorCode): void
    {
        if (isset(self::$messageTemplates[$errorCode]) === true) {
            $message = sprintf(self::$messageTemplates[$errorCode], $key);
        } else {
            $message = $key . ' error';
        }

        $this->messages->addErrorMessage($key, $message);
    }
}
