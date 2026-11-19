<?php
require_once __DIR__ . '/../../GameEngine/Building.php';

use PHPUnit\Framework\TestCase;

class BuildingIntegrationTest extends TestCase
{
    public function testInitialization()
    {
        $db = $this->createMock(\MYSQLi_DB::class);
        $db->method('getJobs')->willReturn([]);
        $db->method('getDemolition')->willReturn([]);
        $sess = new \Session();
        $sess->tribe = 1;
        $sess->uid = 1;
        $sess->plus = false;

        $vil = (object) [
            'wid' => 1,
            'resarray' => ['f19t' => 0],
            'natar' => 0,
            'allcrop' => 1000,
            'pop' => 10,
            'master' => 0,
            'capital' => 0,
        ];

        $log = new \stdClass();
        $building = new Building($db, $sess, $vil, $log, null, null, []);

        $this->assertInstanceOf(Building::class, $building);
    }

    public function testCanBuildWithDemolition()
    {
        $db = $this->createMock(\MYSQLi_DB::class);
        $db->method('getDemolition')->willReturn([['buildnumber' => 1]]);
        $db->method('getJobs')->willReturn([]);

        $sess = new \Session();
        $sess->tribe = 1;
        $sess->plus = false;

        $vil = (object) ['wid' => 1, 'resarray' => ['f19t' => 0]];
        $log = new \stdClass();

        $building = new Building($db, $sess, $vil, $log, null, null, []);

        $this->assertEquals(11, $building->canBuild(1, 1));
    }
}
