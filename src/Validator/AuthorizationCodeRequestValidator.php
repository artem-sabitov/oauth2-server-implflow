<?php

declare(strict_types=1);

namespace OAuth2\Validator;

use OAuth2\Request\AuthorizationRequest;
use OAuth2\Exception\ParameterException;
use OAuth2\Messages;

class AuthorizationCodeRequestValidator
{
    const INVALID_PARAMETER = 1;
    const MISSING_PARAMETER = 2;

    /**
     * @var string
     */
    protected $messageTemplates = [
        self::INVALID_PARAMETER => 'Parameter \'%s\' has invalid value \'%s\'',
        self::MISSING_PARAMETER => 'Required parameter \'%s\' is missing',
    ];

    /**
     * @var Messages
     */
    private $errorMessages;

    /**
     * AuthorizationRequestValidator constructor.
     * @param string $supportedResponseType
     */
    public function __construct(array $messageTemplates = null)
    {
        if ($messageTemplates !== null) {
            $this->messageTemplates = array_merge($this->messageTemplates, $messageTemplates);
        }

        $this->errorMessages = new Messages();
    }

    public function hasErrorMessages(): bool
    {
        return empty($this->errorMessages->toArray()) === false;
    }

    public function getErrorMessages(): array
    {
        return $this->errorMessages->toArray();
    }

    /**
     * @throws ParameterException
     */
    public function validate(AuthorizationRequest $request): void
    {
        if ($this->isValid($request) === false) {
            throw (new ParameterException())->withMessages($this->getErrorMessages());
        }
    }

    public function isValid(AuthorizationRequest $request): bool
    {
        $isValidClientId = $this->validateClientId($request);
        $isValidRedirectUri = $this->validateRedirectUri($request);
        $isValidResponseType = $this->validateResponseType($request);

        return $isValidClientId && $isValidRedirectUri && $isValidResponseType;
    }

    public function validateClientId(AuthorizationRequest $request): bool
    {
        if ($request->getClientId() === '') {
            $key = AuthorizationRequest::CLIENT_ID_KEY;
            $this->addErrorMessage($key, $this->buildMissingParameterMessage($key));

            return false;
        }

        return true;
    }

    public function validateRedirectUri(AuthorizationRequest $request): bool
    {
        if ($request->getRedirectUri() === '') {
            $key = AuthorizationRequest::REDIRECT_URI_KEY;
            $this->addErrorMessage($key, $this->buildMissingParameterMessage($key));

            return false;
        }

        return true;
    }

    public function validateResponseType(AuthorizationRequest $request): bool
    {
        if ($request->getResponseType() === '') {
            $key = AuthorizationRequest::RESPONSE_TYPE_KEY;
            $this->addErrorMessage($key, $this->buildMissingParameterMessage($key));

            return false;
        }

        return true;
    }

    protected function addErrorMessage(string $key, string $message): void
    {
        $this->errorMessages->addErrorMessage($key, $message);
    }

    protected function buildMissingParameterMessage($parameter): string
    {
        return sprintf($this->messageTemplates[self::MISSING_PARAMETER], $parameter);
    }
}
