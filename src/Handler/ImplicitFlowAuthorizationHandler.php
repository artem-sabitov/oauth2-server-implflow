<?php

namespace OAuth2\Handler;

use OAuth2\GrantType\AbstractGrantType;
use OAuth2\Request\AuthorizationRequest;

class SimpleGrandTypeResolver extends AbstractAuthorizationRequestHandler
{
    public function handle(AuthorizationRequest $request): AbstractGrantType
    {
        // TODO: Implement handle() method.
    }
}