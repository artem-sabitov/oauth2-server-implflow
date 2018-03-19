<?php

namespace OAuth2\GrantType;

abstract class AbstractGrantType
{
    /**
     * @var string
     */
    protected $type = 'unsupported';

    public function getTypeAsString(): string
    {
        return $this->type;
    }
}
