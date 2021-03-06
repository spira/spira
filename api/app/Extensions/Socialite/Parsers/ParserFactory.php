<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Extensions\Socialite\Parsers;

use Laravel\Socialite\Contracts\User;
use Spira\Core\Contract\Exception\NotImplementedException;

class ParserFactory
{
    /**
     * Parse the social user with the appropriate parser.
     *
     * @param  User    $user
     * @param  string  $provider
     *
     * @throws NotImplementedException
     *
     * @return AbstractParser
     */
    public static function parse(User $user, $provider)
    {
        // We can't use the ::class when the class name is inside a variable
        // to get the full qualified name, so we have to fallback on using
        // the __NAMESPACE__ constant.
        $parser = __NAMESPACE__.'\\'.ucfirst($provider).'Parser';

        if (class_exists($parser)) {
            return new $parser($user);
        } else {
            throw new NotImplementedException('No parser for '.$provider.' exists.');
        }
    }
}
