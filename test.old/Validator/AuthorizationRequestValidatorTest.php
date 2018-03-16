<?php

namespace OAuth2Test\Validator;

use OAuth2\ClientInterface;
use OAuth2\Provider\ClientProviderInterface;
use OAuth2\Validator\AuthorizationRequestValidator;
use OAuth2Test\Assets\TestClientProvider;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class AuthorizationRequestValidatorTest extends TestCase
{
    /**
     * @var array
     */
    protected $clientProperties;

    /**
     * @var ClientProviderInterface
     */
    protected $clientProvider;

    /**
     * @var
     */
    protected $supportedResponseType;

    protected function setUp()
    {
        $this->clientProperties = [
            'identificator' => 'test',
            'redirect_uri' => 'http://example.com',
        ];

        $this->clientProvider = new TestClientProvider($this->clientProperties);
        $this->supportedResponseType = 'token_test';
    }

    public function getValidator()
    {
        return new AuthorizationRequestValidator(
            $this->clientProvider,
            $this->supportedResponseType
        );
    }

    public function testConstructorAcceptsAnArguments()
    {
        $validator = $this->getValidator();

        $r = new ReflectionProperty($validator, 'clientProvider');
        $r->setAccessible(true);
        $clientProvider = $r->getValue($validator);

        $this->assertSame($this->clientProvider, $clientProvider);

        $r = new ReflectionProperty($validator, 'supportedResponseType');
        $r->setAccessible(true);
        $supportedResponseType = $r->getValue($validator);

        $this->assertEquals($this->supportedResponseType, $supportedResponseType);
    }

    public function testReturnEmptyArrayOfMessagesByDefault()
    {
        $validator = $this->getValidator();
        $this->assertTrue(empty($validator->getErrorMessages()));
    }

//    public function test
}