<?php

namespace Tests\Utils;

use PHPUnit\Framework\TestCase;
use App\Utils\AccessLogger;

class AccessLoggerTest extends TestCase
{
    public function testLogRequestReturnsTrueWhenLoggingDisabled(): void
    {
        // This is a basic test, ensuring it doesn't crash and returns true (default behavior).
        $this->assertTrue(AccessLogger::logRequest());
    }
}
