<?php

use Illuminate\Support\Facades\Log;

class LogTest extends TestCase
{

    public function testInfoLog()
    {

        $logSuccess = Log::info("Test info log");
        $this->assertTrue($logSuccess);
    }

    public function testWarningLog()
    {

        $logSuccess = Log::warning("Test warning log");
        $this->assertTrue($logSuccess);
    }

    public function testErrorLog()
    {

        $logSuccess = Log::error("Test error log");
        $this->assertTrue($logSuccess);
    }

}
