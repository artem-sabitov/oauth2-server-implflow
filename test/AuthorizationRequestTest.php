<?php

namespace OAuth2Test\Grant\Implicit;

use InvalidArgumentException;
use OAuth2\Grant\Implicit\AuthorizationRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequest as Request;

class AuthorizationRequestTest extends TestCase
{
    /**
     * @var ServerRequestInterface
     */
    private $serverRequest;

    /**
     * @var AuthorizationRequest
     */
    protected $authorizationRequest;

    public function setUp()
    {
        $this->serverRequest = new Request([], [], 'http://example.com/', 'GET');
        $this->authorizationRequest = new AuthorizationRequest($this->serverRequest);
    }

    public function testClientIdEmptyByDefault()
    {
        $this->assertSame('', $this->authorizationRequest->getClientId());
    }

    public function testRedirectUriEmptyByDefault()
    {
        $this->assertSame('', $this->authorizationRequest->getRedirectUri());
    }

    public function testResponseTypeEmptyByDefault()
    {
        $this->assertSame('', $this->authorizationRequest->getResponseType());
    }

    public function testValidatorRaisesExceptionForInvalidParameter()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->authorizationRequest->withClientId('');
    }

    public function testWithClientIdReturnsNewInstanceWithNewClientId()
    {
        $firstRequest = $this->authorizationRequest->withClientId('first');
        $this->assertNotSame($this->authorizationRequest, $firstRequest);
        $secondRequest = $firstRequest->withClientId('second');
        $this->assertNotSame($firstRequest, $secondRequest);
        $this->assertSame('second', $secondRequest->getClientId());
    }

    public function testWithRedirectUriReturnsNewInstanceWithNewRedirectUri()
    {
        $firstRequest = $this->authorizationRequest->withRedirectUri('http://first');
        $this->assertNotSame($this->authorizationRequest, $firstRequest);
        $secondRequest = $firstRequest->withRedirectUri('http://second');
        $this->assertNotSame($firstRequest, $secondRequest);
        $this->assertSame('http://second', $secondRequest->getRedirectUri());
    }

    public function testWithResponseTypeReturnsNewInstanceWithResponseType()
    {
        $firstRequest = $this->authorizationRequest->withRedirectUri('code');
        $this->assertNotSame($this->authorizationRequest, $firstRequest);
        $secondRequest = $firstRequest->withResponseType('token');
        $this->assertNotSame($firstRequest, $secondRequest);
        $this->assertSame('token', $secondRequest->getResponseType());
    }

    public function testConstructorSetParamCorrect()
    {
        $request = new Request(
            [],
            [],
            'http://example.com/',
            'GET',
            'php://memory',
            [],
            [],
            [
                'client_id' => 'test',
                'redirect_uri' => 'http://example.com',
                'response_type' => 'token',
            ]
        );

        $this->authorizationRequest = new AuthorizationRequest($request);

        $this->assertSame('test', $this->authorizationRequest->getClientId());
        $this->assertSame('http://example.com', $this->authorizationRequest->getRedirectUri());
        $this->assertSame('token', $this->authorizationRequest->getResponseType());
    }
}
