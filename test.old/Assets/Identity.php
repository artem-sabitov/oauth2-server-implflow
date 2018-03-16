<?php

namespace OAuth2Test\Assets;

use OAuth2\IdentityInterface;

class Identity implements IdentityInterface
{
    protected $id = 'test';

    /**
     * @return mixed
     */
    public function getIdentityId()
    {
        return $this->id;
    }
}
