<?php

namespace OAuth2\Grant\Implicit\Parameter;

interface RedirectUriInterface
{
    /**
     * @return string
     */
    public function getValue();
}