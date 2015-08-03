<?php

namespace App\Extensions\Socialite\Two;

use Laravel\Socialite\Two\GoogleProvider as GoogleProviderBase;
use App\Extensions\Socialite\Contracts\Provider as ProviderContract;

class GoogleProvider extends GoogleProviderBase implements ProviderContract
{
    use ProviderTrait;
}
