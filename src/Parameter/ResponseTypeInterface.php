<?php

namespace OAuth2\Grant\Implicit\Parameter;

interface ResponseTypeInterface
{
    /**
     * @return string
     */
    public function getValue();
}