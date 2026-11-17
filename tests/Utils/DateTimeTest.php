<?php

namespace Tests\Utils;

use PHPUnit\Framework\TestCase;
use App\Utils\DateTime;

class DateTimeTest extends TestCase
{
    public function testGetServerStartTimestamp(): void
    {
        $this->assertEquals(strtotime('2026-01-01 12:00'), DateTime::getServerStartTimestamp('01.01.2026', '12:00', new \DateTimeZone('UTC')));
        $this->assertNull(DateTime::getServerStartTimestamp('invalid', 'invalid'));
    }

    public function testHasServerStarted(): void
    {
        $this->assertTrue(DateTime::hasServerStarted('01.01.2000', '12:00', strtotime('2026-01-01 12:00'), new \DateTimeZone('UTC')));
        $this->assertFalse(DateTime::hasServerStarted('01.01.2050', '12:00', strtotime('2026-01-01 12:00'), new \DateTimeZone('UTC')));
    }

    public function testGetTimeFormat(): void
    {
        $this->assertEquals('0 day 0h 01m 05s', DateTime::getTimeFormat(65));
        $this->assertEquals('1 day 1h 01m 05s', DateTime::getTimeFormat(24*3600 + 3600 + 65));
    }
}
