<?php

declare(strict_types=1);

namespace OAuth2\Validator;

use OAuth2\Exception\ParameterException;
use OAuth2\Handler\AuthorizationCodeGrant;
use OAuth2\Messages;
use OAuth2\Request\AuthorizationRequest;

class RefreshTokenRequestValidator
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
        $isValidGrantType = $this->validateGrantType($request);
        $isValidClientId = $this->validateClientId($request);
        $isValidCode = $this->validateRefreshToken($request);

        return $isValidGrantType && $isValidClientId && $isValidCode;
    }

    public function validateGrantType(AuthorizationRequest $request): bool
    {
        if ($request->get(AuthorizationCodeGrant::GRANT_TYPE_KEY) === '') {
            $key = AuthorizationCodeGrant::GRANT_TYPE_KEY;
            $this->addErrorMessage($key, $this->buildMissingParameterMessage($key));

            return false;
        }

        return true;
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

    public function validateRefreshToken(AuthorizationRequest $request): bool
    {
        $refreshToken = $request->get(AuthorizationCodeGrant::REFRESH_TOKEN);
        if ($refreshToken === null) {
            $key = AuthorizationCodeGrant::REFRESH_TOKEN;
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
