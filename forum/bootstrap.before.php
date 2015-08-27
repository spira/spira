<?php

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
