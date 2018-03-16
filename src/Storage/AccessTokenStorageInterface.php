<?php

namespace OAuth2\Storage;

use OAuth2\Token\AccessToken;

interface AccessTokenStorageInterface
{
    /**
     * @param AccessToken $accessToken
     * @return mixed
     */
    public function write(AccessToken $accessToken);
}
