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
    }
}
