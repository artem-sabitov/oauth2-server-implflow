<?php

namespace OAuth2\Grant\Implicit;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

interface ServerInterface
{
    public function authorize(ServerRequestInterface $request): ResponseInterface;
}
