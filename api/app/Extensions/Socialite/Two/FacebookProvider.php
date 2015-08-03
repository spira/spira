<?php

namespace App\Extensions\Socialite\Two;

use Laravel\Socialite\Two\FacebookProvider as FacebookProviderBase;
use App\Extensions\Socialite\Contracts\Provider as ProviderContract;

class FacebookProvider extends FacebookProviderBase implements ProviderContract
{
    use ProviderTrait;
}
