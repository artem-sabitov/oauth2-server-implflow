<?php

namespace OAuth2\Grant\Implicit;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequestFactory;

class Server implements GrantManagerInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return GrantResultInterface
     */
    public function authorize(ServerRequestInterface $request): GrantResultInterface
    {
        $query = $request->getBody();
        var_dump($query);
        die;
    }
}
