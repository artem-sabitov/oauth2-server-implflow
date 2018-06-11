<?php

namespace OAuth2;

use Zend\Expressive\Authentication\UserInterface;

interface IdentityInterface extends UserInterface
{
    /**
     * @return mixed
     */
    public function getIdentityId();
}
