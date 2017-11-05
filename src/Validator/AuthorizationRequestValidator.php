<?php

namespace OAuth2\Grant\Implicit\Validator;

use OAuth2\Grant\Implicit\AuthorizationRequest;
use OAuth2\Grant\Implicit\Exception\ParameterException;
use OAuth2\Grant\Implicit\Messages;
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
    public function __construct(string $supportedResponseType, string $supportedRedirectUri)
    {
        $this->messages = new Messages();
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

        $this->validateResponseType($request->getResponseType());
        $this->validateRedirectUri($request->getRedirectUri());
    }

    /**
     * @return Messages
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @throws ParameterException
     */
    public function validateResponseType(string $responseType): void
    {
        if ($responseType !== $this->supportedResponseType) {
            $this->messages->addErrorMessage(
                AuthorizationRequest::RESPONSE_TYPE_KEY,
                sprintf(
                    self::$messageTemplates[self::INVALID_PARAMETER],
                    AuthorizationRequest::RESPONSE_TYPE_KEY
                )
            );
        }
    }

    /**
     * @return void
     * @throws ParameterException
     */
    public function validateRedirectUri(string $redirectUri): void
    {
        if ($redirectUri !== $this->supportedRedirectUri) {
            $this->messages->addErrorMessage(
                AuthorizationRequest::RESPONSE_TYPE_KEY,
                sprintf(
                    self::$messageTemplates[self::INVALID_PARAMETER],
                    AuthorizationRequest::RESPONSE_TYPE_KEY
                )
            );
        }
    }
}
