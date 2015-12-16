<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Spira\Core\Helpers\Arr;

$resultArray = [];
foreach (glob(__DIR__.'/permissions/*.php') as $item) {
    $array = require $item;
    $resultArray = Arr::merge($resultArray, $array);
}

return $resultArray;