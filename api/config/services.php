<?php

return [

    'facebook' => [
        'client_id' => env('PROVIDER_FACEBOOK_CLIENT_ID'),
        'client_secret' => env('PROVIDER_FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('PROVIDER_FACEBOOK_CLIENT_REDIRECT'),
    ],

    'twitter' => [
        'client_id' => env('PROVIDER_TWITTER_CLIENT_ID'),
        'client_secret' => env('PROVIDER_TWITTER_CLIENT_SECRET'),
        'redirect' => env('PROVIDER_TWITTER_CLIENT_REDIRECT'),
    ],

    'google' => [
        'client_id' => env('PROVIDER_GOOGLE_CLIENT_ID'),
        'client_secret' => env('PROVIDER_GOOGLE_CLIENT_SECRET'),
        'redirect' => env('PROVIDER_GOOGLE_CLIENT_REDIRECT'),
    ],

];
