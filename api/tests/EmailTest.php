<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Illuminate\Mail\Message;
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

        Mail::send('emails.welcome', [], function (Message $message) use ($subject) {
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
