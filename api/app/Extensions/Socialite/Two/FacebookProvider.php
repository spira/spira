<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Extensions\Socialite\Two;

use Laravel\Socialite\Two\FacebookProvider as FacebookProviderBase;
use App\Extensions\Socialite\Contracts\Provider as ProviderContract;

class FacebookProvider extends FacebookProviderBase implements ProviderContract
{
    use ProviderTrait;
}
