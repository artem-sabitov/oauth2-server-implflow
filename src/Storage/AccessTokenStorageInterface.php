<?php

namespace OAuth2\Grant\Implicit\Storage;

use OAuth2\Grant\Implicit\Token\AccessToken;

interface AccessTokenStorageInterface
{
    /**
     * @param AccessToken $accessToken
     * @return mixed
     */
    public function write(AccessToken $accessToken);
}
