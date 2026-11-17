<?php

namespace Tests\Utils;

use PHPUnit\Framework\TestCase;
use App\Utils\DebugErrorLogger;

class DebugErrorLoggerTest extends TestCase
{
    public function testEnableDoesNotCrash(): void
    {
        // This is a basic test, ensuring it doesn't crash.
        DebugErrorLogger::enable(['max_size_mb' => 1]);
        $this->assertTrue(true); // If we get here, it didn't crash.
    }

    protected function tearDown(): void
    {
        restore_error_handler();
        parent::tearDown();
    }
}
