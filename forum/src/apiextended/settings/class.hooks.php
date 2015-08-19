<?php

class ApiExtendedHooks implements Gdn_IPlugin
{
    /**
     * Register extra DiscussionsAPI endpoints.
     *
     * @param  DiscussionsAPI $sender
     *
     * @return void
     */
    public function DiscussionsAPI_register_handler($sender)
    {
        $sender::get('/foreign/[*:foreignID]', [
            'controller' => 'ApiDiscussion',
            'method' => 'getbyforeignid',
            'arguments' => [
                'Page' => val('Page', $data)
            ]
        ]);
    }

    /**
     * Register extra UsersAPI endpoints.
     *
     * @param  UsersAPI $sender
     *
     * @return void
     */
    public function UsersAPI_register_handler($sender)
    {
        $sender::post('/sso', [
            'controller' => 'ApiUser',
            'method' => 'sso',
            'authenticate' => true,
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
