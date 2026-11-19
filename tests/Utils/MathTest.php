<?php

namespace Tests\Utils;

use PHPUnit\Framework\TestCase;
use App\Utils\Math;

class MathTest extends TestCase
{
    public function testIsIntWithNumericString(): void
    {
        $this->assertTrue(Math::isInt('42'), 'isInt("42") should be true');
    }

    public function testIsIntWithInteger(): void
    {
        $this->assertTrue(Math::isInt(42), 'isInt(42) should be true');
    }

    public function testIsIntWithFloatString(): void
    {
        $this->assertFalse(Math::isInt('42.5'), 'isInt("42.5") should be false');
        $this->assertFalse(Math::isInt(42.0), 'isInt(42.0) should be false');
    }

    public function testIsFloatHandlesFloatsNumericStringsAndNonFiniteValues(): void
    {
        $this->assertTrue(Math::isFloat(42.5));
        $this->assertTrue(Math::isFloat('42.5'));
        $this->assertFalse(Math::isFloat(42));
        $this->assertFalse(Math::isFloat('42'));
        $this->assertFalse(Math::isFloat(INF));
        $this->assertFalse(Math::isFloat(-INF));
        $this->assertFalse(Math::isFloat(NAN));
    }
}
