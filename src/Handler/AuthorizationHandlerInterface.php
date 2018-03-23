<?php

namespace OAuth2\Handler;

use OAuth2\Request\AuthorizationRequest;
use Psr\Http\Message\ResponseInterface;

interface AuthorizationHandlerInterface
{
    public function canHandle(AuthorizationRequest $request): bool;

    public function handle(AuthorizationRequest $request): ResponseInterface;
}
