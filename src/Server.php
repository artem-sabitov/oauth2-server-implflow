<?php

namespace OAuth2\Grant\Implicit;

use Psr\Http\Message\ServerRequestInterface;

class Server implements GrantManagerInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return GrantResultInterface
     */
    public function authorize(ServerRequestInterface $request): GrantResultInterface
    {
        $params = $request->getQueryParams();
    }
}
