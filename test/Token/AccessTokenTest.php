<?php

namespace OAuth2Test\Grant\Implicit\Token;

use DateTime;
use OAuth2\Grant\Implicit\ClientInterface;
use OAuth2\Grant\Implicit\IdentityInterface;
use OAuth2\Grant\Implicit\Token\AccessToken;
use PHPUnit\Framework\TestCase;

class AccessTokenTest extends TestCase
{
    /**
     * @var string
     */
    protected $tokenValue;

    /**
     * @var IdentityInterface
     */
    protected $identity;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var int
     */
    protected $expires;

    /**
     * @var AccessToken
     */
    protected $token;

    public function setUp()
    {
        $this->tokenValue = 'test';
        $this->identity = $this->createMock(IdentityInterface::class);
        $this->client = $this->createMock(ClientInterface::class);
        $this->expires = (new DateTime())->getTimestamp();

        $this->token = new AccessToken(
            $this->tokenValue,
            $this->identity,
            $this->client,
            $this->expires
        );
    }

    public function testAccessTokenHasSameClient()
    {
        $this->assertSame($this->client, $this->token->getClient());
    }

    public function testAccessTokenHasSameIdentity()
    {
        $this->assertSame($this->identity, $this->token->getIdentity());
    }

    public function testAccessTokenHasEqualsTokenValue()
    {
        $this->assertEquals($this->tokenValue, $this->token->getAccessToken());
    }

    public function testAccessTokenHasEqualsExpires()
    {
        $this->assertEquals($this->expires, $this->token->getExpires());
    }
}
