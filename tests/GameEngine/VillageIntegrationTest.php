<?php

namespace Tests\GameEngine;

require_once __DIR__ . '/../../GameEngine/Technology.php';
require_once __DIR__ . '/../../GameEngine/Building.php';
require_once __DIR__ . '/../../GameEngine/Village.php';
require_once __DIR__ . '/../../GameEngine/Logging.php';

use PHPUnit\Framework\TestCase;
use Village;

class VillageIntegrationTest extends TestCase
{
    private function createFullResourceLevel()
    {
        $res = [];
        for ($i = 1; $i <= 40; $i++) {
            $res['f' . $i] = ($i === 1) ? 1 : 0;
            $res['f' . $i . 't'] = ($i === 1) ? 1 : 0;
        }
        return $res;
    }

    private function createConfiguredMockDatabase(int $lastUpdateOffset = 0)
    {
        $database = $this->getMockBuilder(\MYSQLi_DB::class)
            ->disableOriginalConstructor()
            ->getMock();

        $database->method('checkVilExist')->willReturn(true);
        $database->method('getVillage')->willReturn([
            'wref' => 1,
            'owner' => 1,
            'name' => 'Test Village',
            'capital' => 1,
            'natar' => 0,
            'celebration' => 0,
            'festival' => 0,
            'wood' => 500,
            'clay' => 500,
            'iron' => 500,
            'crop' => 500,
            'pop' => 10,
            'maxstore' => 800,
            'maxcrop' => 800,
            'loyalty' => 100,
            'lastupdate' => time() - $lastUpdateOffset,
        ]);
        $database->method('getResourceLevel')->willReturn($this->createFullResourceLevel());
        $database->method('getCoor')->willReturn(['x' => 0, 'y' => 0]);
        $database->method('getVillageType')->willReturn(1);
        $database->method('getOasis')->willReturn([]);
        $database->method('getUnit')->willReturn([]);
        $database->method('getEnforceVillage')->willReturn([]);
        $database->method('getOasisEnforce')->willReturn([]);
        $database->method('getTech')->willReturn([]);
        $database->method('getABTech')->willReturn([]);
        $database->method('getResearching')->willReturn([]);
        $database->method('getMasterJobs')->willReturn([]);
        $database->method('getProfileVillages')->willReturn([]);
        $database->method('getUserArray')->willReturn([
            'b1' => 0,
            'b2' => 0,
            'b3' => 0,
            'b4' => 0,
        ]);

        return $database;
    }

    private function createBidLevels()
    {
        $prodLevels = [0 => ['prod' => 5], 1 => ['prod' => 20]];
        return [
            1 => $prodLevels,
            2 => $prodLevels,
            3 => $prodLevels,
            4 => $prodLevels,
            5 => [],
            6 => [],
            7 => [],
            8 => [],
            9 => [],
            45 => [],
        ];
    }

    public function testVillageInitialization()
    {
        $database = $this->createConfiguredMockDatabase();
        $session = new \Session();
        $session->uid = 1;
        $session->villages = [1];
        $session->isAdmin = false;

        $technology = $this->createMock(\Technology::class);
        $technology->method('getAllUnits')->willReturn(['hero' => 0]);
        $logging = $this->createMock(\Logging::class);

        $village = new Village($database, $session, $technology, $this->createBidLevels(), $logging);

        $this->assertInstanceOf(Village::class, $village);
        $this->assertEquals('Test Village', $village->vname);
        $this->assertEquals(1, $village->wid);
    }

    public function testTickComputesAndAppliesProduction()
    {
        $database = $this->createConfiguredMockDatabase(3600);
        $database->expects($this->once())
            ->method('modifyResource')
            ->with(
                1,
                $this->greaterThan(0),
                $this->anything(),
                $this->anything(),
                $this->anything(),
                1,
                true
            );

        $session = new \Session();
        $session->uid = 1;
        $session->villages = [1];
        $session->isAdmin = false;

        $technology = $this->createMock(\Technology::class);
        $technology->method('getAllUnits')->willReturn(['hero' => 0]);
        $technology->method('getUpkeep')->willReturn(0);
        $logging = $this->createMock(\Logging::class);

        $village = new Village($database, $session, $technology, $this->createBidLevels(), $logging);
        $village->tick();

        $this->assertGreaterThan(0, $village->getProd('wood'));
    }
}
