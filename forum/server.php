<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

// built-in PHP web server. This provides a convenient way to test Vanilla
// forum without having installed a "real" server software here.
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

// Remove starting slash if it exists, and set the url as the page to retrieve
$uri = ltrim($uri, '/');
$_GET['p'] = $uri;

// "Bootstrap" the root path to make sure we get the public folder
define('PATH_ROOT', dirname(__FILE__).'/public');

require_once __DIR__.'/public/index.php';
