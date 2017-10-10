<?php

namespace OAuth2\Grant\Implicit\Parameter;

interface ClientIdInterface
{
    /**
     * @return string
     */
    public function getValue();
}