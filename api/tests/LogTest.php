<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

use Illuminate\Support\Facades\Log;

class LogTest extends TestCase
{
    public function testInfoLog()
    {
        $logSuccess = Log::info('Test info log');
        $this->assertTrue($logSuccess);
    }

    public function testWarningLog()
    {
        $logSuccess = Log::warning('Test warning log');
        $this->assertTrue($logSuccess);
    }

    public function testErrorLog()
    {
        $logSuccess = Log::error('Test error log');
        $this->assertTrue($logSuccess);
    }
}
