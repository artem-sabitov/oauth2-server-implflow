<?php

namespace OAuth2Test;

use InvalidArgumentException;
use OAuth2\AuthorizationRequest;
use OAuth2\Exception\ParameterException;
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
        $this->serverRequest = new Request(
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
        $this->authorizationRequest = new AuthorizationRequest($this->serverRequest);
    }

    public function testValidatorRaisesExceptionForInvalidParameter()
    {
        $this->expectException(ParameterException::class);

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