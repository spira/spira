<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Services\SingleSignOn;

use Illuminate\Http\Request;
use App\Exceptions\NotImplementedException;

class SingleSignOnFactory
{
    /**
     * Create the single sign on handler for the requester.
     *
     * @param  string   $requester
     * @param  Request  $request
     * @param  mixed    $user
     *
     * @throws NotImplementedException
     *
     * @return ParserContract
     */
    public static function create($requester, Request $request, $user)
    {
        // We can't use the ::class when the class name is inside a variable
        // to get the full qualified name, so we have to fallback on using
        // the __NAMESPACE__ constant.
        $requester = __NAMESPACE__.'\\'.ucfirst($requester).'SingleSignOn';

        if (class_exists($requester)) {
            return new $requester($request, $user);
        } else {
            throw new NotImplementedException('No single sign on exists for '.$requester.'.');
        }
    }
}
