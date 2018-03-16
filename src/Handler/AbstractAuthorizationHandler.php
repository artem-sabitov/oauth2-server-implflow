<?php

namespace OAuth2\Handler;

use OAuth2\GrantType\AbstractGrantType;
use OAuth2\Request\AuthorizationRequest;

abstract class AbstractAuthorizationRequestHandler
{
    public abstract function handle(AuthorizationRequest $request): AbstractGrantType;
}