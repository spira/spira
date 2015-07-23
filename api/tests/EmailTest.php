<?php

use Illuminate\Support\Facades\Mail;

/**
 * Class EmailTest.
 */
class EmailTest extends TestCase
{
    use MailcatcherTrait;

    /**
     * Test smtp connection to mailcatcher is working.
     */
    public function testSendEmail()
    {
        $subject = 'Test Email';

        Mail::send('emails.welcome', [], function ($message) use ($subject) {
            $message->to('foo@example.com', 'John Smith')->subject($subject);
        });

        $message = $this->getLastMessage();

        $this->assertTrue(is_object($message), 'Message is an object');

        $this->assertEquals($subject, $message->subject);
    }

    /**
     * Assert that the mailbox is empty.
     *
     * @expectedException PHPUnit_Framework_AssertionFailedError
     */
    public function testEmptyMailbox()
    {
        $this->clearMessages();
        $this->getLastMessage();
    }
}
