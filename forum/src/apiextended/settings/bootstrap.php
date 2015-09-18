<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

if (! defined('APPLICATION')) {
    exit();
}

// If running the forum on PHP cli server, the content type server variable
// is not set as CONTENT_TYPE like the API expects, but as HTTP_CONTENT_TYPE.
// To rememdy this so we can handle API requests via the CLI server we copy the
// value in this case
$apiExtendedRequest = Gdn::request();
$apiExtendedContentType = $apiExtendedRequest->getValueFrom('server', 'HTTP_CONTENT_TYPE');

if ($apiExtendedContentType) {
    $apiExtendedRequest->setValueOn('server', 'CONTENT_TYPE', $apiExtendedContentType);
}
