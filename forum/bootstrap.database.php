<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

$databaseConfig = [
    'Database.Name' => getenv('FORUM_DB_DATABASE'),
    'Database.Host' => getenv('FORUM_DB_HOST'),
    'Database.User' => getenv('FORUM_DB_USERNAME'),
    'Database.Password' => getenv('FORUM_DB_PASSWORD'),
];

saveToConfig($databaseConfig, null, ['Save' => false]);
