<?php

namespace OAuth2\Grant\Implicit\Factory;

use InvalidArgumentException;
use OAuth2\Grant\Implicit\AuthorizationRequest;
use Psr\Http\Message\ServerRequestInterface;

class AuthorizationRequestFactory
{
    /**
     * @param ServerRequestInterface $request
     * @return AuthorizationRequest
     * @throws InvalidArgumentException
     */
    public static function fromServerRequest(ServerRequestInterface $request)
    {
        return new AuthorizationRequest($request);
    }
}
