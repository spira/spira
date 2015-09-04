<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

return [

    'facebook' => [
        'client_id' => env('PROVIDER_FACEBOOK_CLIENT_ID'),
        'client_secret' => env('PROVIDER_FACEBOOK_CLIENT_SECRET'),
    ],

    'twitter' => [
        'client_id' => env('PROVIDER_TWITTER_CLIENT_ID'),
        'client_secret' => env('PROVIDER_TWITTER_CLIENT_SECRET'),
    ],

    'google' => [
        'client_id' => env('PROVIDER_GOOGLE_CLIENT_ID'),
        'client_secret' => env('PROVIDER_GOOGLE_CLIENT_SECRET'),
    ],

];
