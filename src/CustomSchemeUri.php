<?php

declare(strict_types=1);

namespace OAuth2;

use Zend\Diactoros\Uri;

class CustomSchemeUri extends Uri
{
    public function __construct($uri = '', array $allowedSchemes)
    {
        $this->allowedSchemes = $allowedSchemes;

        parent::__construct($uri);
    }
}
