<?php
declare(strict_types=1);

use App\Utils\DateTime;
use PHPUnit\Framework\TestCase;

final class DateTimeTest extends TestCase
{
    public function testZero(): void
    {
        $this->assertSame('0 day 0h 00m 00s', DateTime::getTimeFormat(0));
    }

    public function testOneHourOneMinuteOneSecond(): void
    {
        $this->assertSame('0 day 1h 01m 01s', DateTime::getTimeFormat(3661));
    }

    public function testOneDay(): void
    {
        $this->assertSame('1 day 0h 00m 00s', DateTime::getTimeFormat(86400));
    }
}
