<?php

use GuzzleHttp\Client;

/**
 * Adds mailcatcher calls.
 */
trait MailcatcherTrait
{
    /**
     * @var \Guzzle\Http\Client
     */
    private $mailcatcher;

    public function bootMailcatcherTrait()
    {
        $this->mailcatcher = new Client([
            'base_url' => 'http://'.getenv('MAIL_HOST').':1080',
        ]);
        // clean emails between tests
        $this->clearMessages();
    }

    /**
     * Empty the inbox.
     */
    public function clearMessages()
    {
        $this->mailcatcher->delete('/messages');
    }

    /**
     * Get the latest email.
     *
     * @return mixed
     */
    public function getLastMessage()
    {
        $messages = $this->getMessages();
        if (empty($messages)) {
            return $this->fail('No messages received');
        }

        // messages are in descending order
        $id = reset($messages)->id;

        return $this->getMessage($id);
    }

    /**
     * Get all emails.
     *
     * @return mixed
     */
    public function getMessages()
    {
        $jsonResponse = $this->mailcatcher->get('/messages');

        return json_decode($jsonResponse->getBody());
    }

    /**
     * Get a message by its id.
     *
     * @param  int     $id
     * @param  string  $type
     * @return mixed
     */
    public function getMessage($id, $type = 'json')
    {
        $jsonResponse = $this->mailcatcher->get(sprintf('/messages/%s.%s', $id, $type));

        return json_decode($jsonResponse->getBody());
    }

    /**
     * Get a message source by its id.
     *
     * @param  int     $id
     * @return string
     */
    public function getMessageSource($id)
    {
        $response = $this->mailcatcher->get(sprintf('/messages/%s.html', $id));

        return (string) $response->getBody();
    }
}
