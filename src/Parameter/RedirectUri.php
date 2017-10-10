<?php

namespace OAuth2\Grant\Implicit\Parameter;

class RedirectUri implements RedirectUriInterface
{
    /**
     * @var string
     */
    protected $value;

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return $this->value;
    }
}