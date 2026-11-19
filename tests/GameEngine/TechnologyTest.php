<?php
namespace Tests;

require_once __DIR__ . '/../../GameEngine/Technology.php';

use PHPUnit\Framework\TestCase;

class TechnologyTest extends TestCase
{
    private $db;
    private $tech;

    protected function setUp(): void
    {
        parent::setUp();
        if (!defined('U1')) define('U1', 'Unit1');
        if (!defined('SPEED')) define('SPEED', 1);

        $this->db = $this->createMock(\MYSQLi_DB::class);
        $village = (object) [
            'wid' => 1,
            'researching' => [['tech' => 'a1'], ['tech' => 't1']],
            'awood' => 100,
            'aclay' => 100,
            'airon' => 100,
            'acrop' => 100,
        ];
        $this->tech = new \Technology(
            $this->db,
            $village,
            $this->createMock(\Session::class),
            new \UnitDataService(),
            $this->createMock(\Building::class),
            [],
            null
        );
    }

    public function testGrabAcademyResFiltersAcademyResearchOnly()
    {
        $result = $this->tech->grabAcademyRes();
        $this->assertCount(1, $result);
        $this->assertEquals('t1', $result[0]['tech']);
    }

    public function testGetABUpgrades()
    {
        $result = $this->tech->getABUpgrades('a');
        $this->assertCount(1, $result);
        $this->assertEquals('a1', $result[0]['tech']);
    }

    public function testIsResearch()
    {
        $village = (object) ['wid' => 1, 'researching' => [['tech' => 't1'], ['tech' => 't2']]];
        $tech = new \Technology(
            $this->db,
            $village,
            $this->createMock(\Session::class),
            new \UnitDataService(),
            $this->createMock(\Building::class),
            [],
            null
        );

        $this->assertTrue($tech->isResearch(1, 1));
        $this->assertFalse($tech->isResearch(3, 1));
    }

    public function testMaxUnitUsesUnitDataAndNormalVsGreatCostMultiplier()
    {
        $db = $this->getMockBuilder(\MYSQLi_DB::class)->disableOriginalConstructor()->getMock();
        $db->method('getVillage')->willReturn([
            'wood' => 1200,
            'clay' => 1000,
            'iron' => 1500,
            'crop' => 300,
            'maxstore' => 8000,
            'maxcrop' => 8000,
        ]);
        $village = new class {
            public $wid = 1;
            public $researching = [];
            public function getProd($type) { return 100; }
        };

        $tech = new \Technology(
            $db,
            $village,
            $this->createMock(\Session::class),
            new \UnitDataService(),
            $this->createMock(\Building::class),
            [],
            null
        );

        // u1 Legionnaire cost: 120 wood, 100 clay, 150 iron, 30 crop => min(10,10,10,10) = 10
        $this->assertEquals(10, $tech->maxUnit(1, false));
        // Great barracks 3x cost => floor(300 / 90) = 3
        $this->assertEquals(3, $tech->maxUnit(1, true));
    }

    public function testGetTrainingTimeReadsAllBuildingArraysViaBidData()
    {
        $building = $this->createMock(\Building::class);
        $building->method('getTypeLevel')->willReturnMap([
            [19, 5],
            [29, 3],
            [20, 4],
            [30, 2],
            [41, 1],
            [21, 3],
            [49, 2],
            [25, 10],
            [26, 0],
            [44, 0],
            [36, 2],
        ]);

        $bidData = [
            'bid19' => [2 => ['attri' => 90], 5 => ['attri' => 70]],
            'bid29' => [3 => ['attri' => 80]],
            'bid20' => [4 => ['attri' => 75]],
            'bid30' => [2 => ['attri' => 85]],
            'bid41' => [1 => ['attri' => 2]],
            'bid21' => [3 => ['attri' => 80]],
            'bid49' => [2 => ['attri' => 88]],
            'bid25' => [10 => ['attri' => 50]],
            'bid26' => [1 => ['attri' => 100]],
            'bid44' => [1 => ['attri' => 100]],
        ];

        $village = (object) ['wid' => 0, 'researching' => []];
        $tech = new \Technology(
            null,
            $village,
            $this->createMock(\Session::class),
            new \UnitDataService(),
            $building,
            $bidData,
            null
        );

        $rm = new \ReflectionMethod(\Technology::class, 'getTrainingTime');
        $rm->setAccessible(true);

        // u1 (foot), u4 (cavalry), u7 (workshop), u9 (special), u99 (trapper)
        $this->assertGreaterThan(0, $rm->invoke($tech, 1, false));
        $this->assertGreaterThan(0, $rm->invoke($tech, 1, true));
        $this->assertGreaterThan(0, $rm->invoke($tech, 4, false));
        $this->assertGreaterThan(0, $rm->invoke($tech, 4, true));
        $this->assertGreaterThan(0, $rm->invoke($tech, 7, false));
        $this->assertGreaterThan(0, $rm->invoke($tech, 7, true));
        $this->assertGreaterThan(0, $rm->invoke($tech, 9, false));
        $this->assertGreaterThan(0, $rm->invoke($tech, 99, false));
    }
}
