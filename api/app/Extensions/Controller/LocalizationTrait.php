<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace App\Extensions\Controller;

use Illuminate\Http\Request;

trait LocalizationTrait
{

    public function getAllLocalizations(Request $request, $id)
    {

    }

    public function getOneLocalization(Request $request, $id, $region)
    {

    }

    public function putOneLocalization(Request $request, $id, $region)
    {
        dd($this->getModel());
    }

}
