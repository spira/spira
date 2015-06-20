<?php

use Illuminate\Support\Facades\Mail;


class EmailTest extends TestCase
{
    use MailcatcherTrait;

    public function setUp()
    {
        parent::setUp();

        $this->setUpMailcatcherTrait(); //@todo find a way to construct mailcatcher trait when used
    }

    /**
     * Test smtp connection to mailcatcher is working
     */
    public function testSendEmail()
    {


        $subject = 'Test Email';

        $responseCode = Mail::send('emails.welcome', [], function($message) use ($subject)
        {
            $message->to('foo@example.com', 'John Smith')->subject($subject);
        });

        $this->assertEquals($responseCode, 1, 'email send response code is 1');

        $message = $this->getLastMessage();

        $this->assertTrue(is_object($message), 'Message is an object');

        $this->assertEquals($subject, $message->subject);

    }

}
