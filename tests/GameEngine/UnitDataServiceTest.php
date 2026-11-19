<?php
namespace Tests\GameEngine;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../GameEngine/UnitDataService.php';

class UnitDataServiceTest extends TestCase
{
    public function testLoadsStandardTribesExpandedTribesAndTraps()
    {
        $svc = new \UnitDataService();

        $legion = $svc->getUnitData(1);
        $this->assertIsArray($legion);
        $this->assertGreaterThan(0, $legion['wood']);

        $vikingSettler = $svc->getUnitData(90);
        $this->assertIsArray($vikingSettler);

        $trap = $svc->getUnitData(99);
        $this->assertIsArray($trap);

        $this->assertNull($svc->getUnitData(95));
        $this->assertNull($svc->getUnitData(999));
    }
}
