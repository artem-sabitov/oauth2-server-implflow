<?php

namespace OAuth2Test\Assets;

use OAuth2\Repository\AuthorizationCodeRepositoryInterface;
use OAuth2\Token\AuthorizationCode;

class TestAuthorizationCodeRepository implements AuthorizationCodeRepositoryInterface
{
    /**
     * @var null|array
     */
    private $repo;

    public function __construct($identityMock, $clientMock)
    {
        $testCode = new AuthorizationCode(
            'test',
            $identityMock,
            $clientMock,
            (new \DateTime())->modify('+1 day')->getTimestamp()
        );
        $expiredCode = new AuthorizationCode(
            'expired',
            $identityMock,
            $clientMock,
            1522540800
        );
        $usedCode = new AuthorizationCode(
            'used',
            $identityMock,
            $clientMock,
            1522540800
        );
        $usedCode->setUsed(true);

        $this->repo = [
            'test' => $testCode,
            'expired' => $expiredCode,
            'used' => $usedCode,
        ];
    }

    public function write(AuthorizationCode $code): AuthorizationCode
    {
        return $code;
    }

    public function find(string $code): ?AuthorizationCode
    {
        if (isset($this->repo[$code]) === false) {
            return null;
        }

        return $this->repo[$code];
    }
}
