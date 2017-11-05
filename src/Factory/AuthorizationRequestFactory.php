<?php

namespace OAuth2\Grant\Implicit\Factory;

use InvalidArgumentException;
use OAuth2\Grant\Implicit\AuthorizationRequest;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequestFactory;

class AuthorizationRequestFactory
{
    /**
     * @return AuthorizationRequest
     * @throws InvalidArgumentException
     */
    public static function fromGlobalServerRequest()
    {
        return self::fromServerRequest(
            ServerRequestFactory::fromGlobals()
        );
    }

    /**
     * @param ServerRequestInterface $serverRequest
     * @return AuthorizationRequest
     * @throws InvalidArgumentException
     */
    public static function fromServerRequest(ServerRequestInterface $serverRequest)
    {
        return new AuthorizationRequest($serverRequest);
    }
}
