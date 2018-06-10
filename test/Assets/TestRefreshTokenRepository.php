<?php

namespace OAuth2Test\Assets;

use OAuth2\Repository\AccessTokenRepositoryInterface;
use OAuth2\Repository\RefreshTokenRepositoryInterface;
use OAuth2\Token\AccessToken;
use OAuth2\Token\RefreshToken;

class TestRefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    /**
     * @var null|array
     */
    private $repo;

    public function __construct($identityMock, $clientMock)
    {
        $accessToken = new AccessToken(
            'test_access_token',
            $identityMock,
            $clientMock,
            (new \DateTime())->modify('-1 day')->getTimestamp()
        );

        $testToken = new RefreshToken(
            'test',
            $accessToken,
            (new \DateTime())->modify('+1 day')->getTimestamp()
        );
        $expiredToken = new RefreshToken(
            'expired',
            $accessToken,
            (new \DateTime())->modify('-1 day')->getTimestamp()
        );
        $usedToken = new RefreshToken(
            'used',
            $accessToken,
            (new \DateTime())->modify('-1 day')->getTimestamp()
        );
        $usedToken->setUsed(true);

        $this->repo = [
            'test' => $testToken,
            'expired' => $expiredToken,
            'used' => $usedToken,
        ];
    }

    public function write(RefreshToken $token): RefreshToken
    {
        return $token;
    }

    public function find(string $token): ?RefreshToken
    {
        if (isset($this->repo[$token]) === false) {
            return null;
        }

        return $this->repo[$token];
    }
}
