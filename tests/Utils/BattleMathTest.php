<?php

namespace Tests\Utils;

use PHPUnit\Framework\TestCase;
use App\Utils\BattleMath;

class BattleMathTest extends TestCase
{
    public function testSigma(): void
    {
        $this->assertEquals(0.5, BattleMath::sigma(1));
        // sigma(0.5) = pow(0.5, 1.5) / 2 = 0.35355 / 2 = 0.17677
        $this->assertEqualsWithDelta(0.17677, BattleMath::sigma(0.5), 0.0001);
        // sigma(2) = (2 - pow(2, -1.5)) / 2 = (2 - 0.35355) / 2 = 0.82322
        $this->assertEqualsWithDelta(0.82322, BattleMath::sigma(2), 0.0001);
    }

    public function testApplyWallAndResidence(): void
    {
        $result = BattleMath::applyWallAndResidence(100, 100, 1, 1, 1, 2);
        $this->assertArrayHasKey('dp', $result);
        $this->assertArrayHasKey('cdp', $result);
        $this->assertGreaterThan(100, $result['dp']);
    }

    public function testComputeMorale(): void
    {
        $this->assertEquals(1.0, BattleMath::computeMorale(100, 200, false, 100, 100));
        // attpop=200, defpop=100.
        // Expected: 1.148
        $this->assertEqualsWithDelta(1.148, BattleMath::computeMorale(200, 100, false, 100, 100), 0.001);
    }

    public function testCalculateCatapultsDamage(): void
    {
        $damage = BattleMath::calculateCatapultsDamage(10, 1, 1, 1, 1, 1);
        // divider = 1*1 = 1
        // efficiency = floor(10/1) = 10
        // sigma(1) = 0.5
        // 4 * 0.5 * 10 * 1 / 1 = 20
        $this->assertEquals(20, $damage);
    }
}
