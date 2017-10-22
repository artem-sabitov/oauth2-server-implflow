<?php

namespace OAuth2Test\Grant\Implicit\Assets;

use OAuth2\Grant\Implicit\IdentityInterface;

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
