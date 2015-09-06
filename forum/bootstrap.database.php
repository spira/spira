<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

$databaseConfig = [
    'Database.Name' => getenv('VANILLADATABASE_ENV_MYSQL_DATABASE'),
    'Database.Host' => getenv('VANILLADATABASE_PORT_3306_TCP_ADDR'),
    'Database.User' => getenv('VANILLADATABASE_ENV_MYSQL_USER'),
    'Database.Password' => getenv('VANILLADATABASE_ENV_MYSQL_PASSWORD'),
];

saveToConfig($databaseConfig, null, ['Save' => false]);
