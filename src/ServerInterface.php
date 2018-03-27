<?php

namespace OAuth2;

use OAuth2\Handler\AuthorizationHandlerInterface;
use OAuth2\Request\AuthorizationRequest;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Expressive\Authentication\UserInterface;

interface ServerInterface
{
    public function registerHandler(string $responseType, AuthorizationHandlerInterface $handler): ServerInterface;

    public function authorize(UserInterface $user, ServerRequestInterface $request): ResponseInterface;

    public function createAuthorizationRequest(ServerRequestInterface $request): AuthorizationRequest;
}
