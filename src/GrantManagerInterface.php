<?php

namespace OAuth2\Grant\Implicit;

use Psr\Http\Message\ServerRequestInterface;

interface GrantManagerInterface
{
    public function authorize(ServerRequestInterface $request): GrantResultInterface;
}
