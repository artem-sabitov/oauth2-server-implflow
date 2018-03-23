<?php

namespace OAuth2;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Expressive\Authentication\UserInterface;

interface ServerInterface
{
    public function authorize(UserInterface $user, ServerRequestInterface $request): ResponseInterface;
}
