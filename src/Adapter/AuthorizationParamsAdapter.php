<?php

namespace OAuth2\Grant\Implicit\Adapter;

use Zend\Diactoros\ServerRequest;

class AuthorizationParamsAdapter
{
    const CLIENT_ID_KEY = 'client_id';
    const REDIRECT_URI_KEY = 'redirect_uri';
    const RESPONSE_TYPE_KEY = 'response_type';

    /**
     * AuthorizationParamsAdapter constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
    }
}
