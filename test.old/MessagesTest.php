<?php

namespace OAuth2Test;

use OAuth2\Messages;
use PHPUnit\Framework\TestCase;

class MessagesTest extends TestCase
{
    /**
     * @var Messages
     */
    protected $messages;

    protected function setUp()
    {
        $this->messages = new Messages();
    }

    public function testToArrayMethodReturnArray()
    {
        $this->assertInternalType('array', $this->messages->toArray());
    }

    public function testAddedMessageWillBeReturnedInMessageList()
    {
        $message = 'Hello World!';
        $key = 'test';

        $this->messages->addErrorMessage($key, $message);
        $this->assertArrayHasKey($key, $this->messages->toArray());
        $this->assertEquals($message, $this->messages->toArray()[$key]);
    }
}
