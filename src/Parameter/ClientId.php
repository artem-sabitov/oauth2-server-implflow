<?php

namespace OAuth2\Grant\Implicit\Parameter;

use OAuth2\Parameter\ClientIdInterface;

class ClientId implements ClientIdInterface
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