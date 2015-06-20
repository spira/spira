<?php

use GuzzleHttp\Client;

/**
 * Adds mailcatcher calls
 */
trait MailcatcherTrait
{

    /**
     * @var \Guzzle\Http\Client
     */
    private $mailcatcher;

    public function setUpMailcatcherTrait()
    {
        $this->mailcatcher = new Client([
            'base_url' => 'http://'.getenv('MAIL_HOST').':1080'
        ]);
        // clean emails between tests
        $this->clearMessages();
    }

    // api calls
    public function clearMessages()
    {
        $this->mailcatcher->delete('/messages');
    }

    public function getLastMessage()
    {
        $messages = $this->getMessages();
        if (empty($messages)) {
            $this->fail("No messages received");
        }
        // messages are in descending order
        return reset($messages);
    }

    public function getMessages()
    {
        $jsonResponse = $this->mailcatcher->get('/messages');
        return json_decode($jsonResponse->getBody());
    }
}
