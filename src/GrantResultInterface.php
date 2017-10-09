<?php

namespace OAuth2\Grant\Implicit;

interface GrantResultInterface
{
    public function isValid(): boolean;
}
