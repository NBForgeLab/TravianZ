<?php

namespace Tests\Utils;

use App\Utils\DebugErrorLogger;
use PHPUnit\Framework\TestCase;

class DebugErrorLoggerTest extends TestCase
{
    public function testEnableRegistersErrorHandlerAndCapturesContext(): void
    {
        DebugErrorLogger::enable(['max_size_mb' => 1, 'lvl_warning' => false], 7, 'Tester');

        $previousHandler = set_error_handler(static fn () => false);
        restore_error_handler();

        $this->assertSame([DebugErrorLogger::class, 'handleError'], $previousHandler);
        $this->assertFalse(DebugErrorLogger::handleError(E_USER_WARNING, 'Sample warning', __FILE__, __LINE__));
    }

    protected function tearDown(): void
    {
        restore_error_handler();
        parent::tearDown();
    }
}
