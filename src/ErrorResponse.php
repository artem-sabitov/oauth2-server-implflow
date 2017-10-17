<?php

namespace OAuth2\Grant\Implicit;

use Zend\Diactoros\Response;

class ErrorResponse extends Response
{
    const UNSUPPORTED_RESPONSE_TYPE = 'unsupported_response_type';
}
