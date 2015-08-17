<?php

class ApiExtendedHooks implements Gdn_IPlugin
{
    /**
     * Register extra DiscussionsAPI API endpoints
     *
     * @param  DiscussionsAPI $sender
     *
     * @return void
     */
    public function DiscussionsAPI_register_handler($sender)
    {
        $sender::get('/foreign/[*:foreignID]', [
            'controller' => 'ApiDiscussion',
            'method'     => 'getbyforeignid',
            'arguments'  => [
                'Page' => val('Page', $data)
            ]
        ]);
    }

    /**
     * Code to be run upon enabling API Extended.
     *
     * @return void
     */
    public function setup()
    {
        return;
    }
}
