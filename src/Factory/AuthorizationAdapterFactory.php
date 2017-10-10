<?php

namespace OAuth2\Grant\Implicit\Factory;

use OAuth2\Grant\Implicit\Adapter\AuthorizationAdapter;
use Psr\Http\Message\ServerRequestInterface;

class AuthorizationAdapterFactory
{
    /**
     * @param ServerRequestInterface $request
     * @return AuthorizationAdapter
     */
    public static function fromServerRequest(ServerRequestInterface $request)
    {
        return new AuthorizationAdapter($request->getQueryParams());
    }
}
