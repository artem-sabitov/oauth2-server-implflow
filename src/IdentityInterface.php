<?php

namespace OAuth2\Grant\Implicit;

interface IdentityInterface
{
    /**
     * @return mixed
     */
    public function getIdentityId();
}
