<?php
declare(strict_types=1);

use App\Utils\Math;
use PHPUnit\Framework\TestCase;

final class MathTest extends TestCase
{
    public function testIsInt(): void
    {
        $this->assertTrue(Math::isInt(5));
        $this->assertFalse(Math::isInt('5'));
        $this->assertFalse(Math::isInt(5.0));
    }

    public function testIsFloat(): void
    {
        $this->assertTrue(Math::isFloat(1.5));
        $this->assertFalse(Math::isFloat('1.5'));
        $this->assertFalse(Math::isFloat(1));
    }
}
