<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

/**
 * If a ForeignID is longer than 36 characters, use its hash instead.
 *
 * @param  $ForeignID string
 *
 * @return string
 */
function foreignIDHash($ForeignID)
{
    return strlen($ForeignID) > 36 ? md5($ForeignID) : $ForeignID;
}
