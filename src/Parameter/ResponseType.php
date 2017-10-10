<?php

namespace OAuth2\Grant\Implicit\Parameter;

class ResponseType implements ResponseTypeInterface
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