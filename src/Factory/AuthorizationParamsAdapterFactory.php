<?php

namespace OAuth2\Grant\Implicit\Factory;

use OAuth2\Grant\Implicit\Adapter\AuthorizationParamsAdapter;
use Zend\Diactoros\ServerRequest;

class AuthorizationParamsAdapterFactory
{
    /**
     * @param ServerRequest $request
     * @return AuthorizationParamsAdapter
     */
    public static function fromServerRequest(ServerRequest $request)
    {
        return new AuthorizationParamsAdapter($request->getQueryParams());
    }
}
