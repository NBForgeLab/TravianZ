<?php
require_once __DIR__ . '/../../GameEngine/Building.php';
require_once __DIR__ . '/../DatabaseTestCase.php';

use Tests\DatabaseTestCase;

class BuildingIntegrationTest extends DatabaseTestCase
{
    public function testInitialization()
    {
        // Setup minimal dependencies for integration
        $db = $GLOBALS['database'];
        
        // Create mocks for other dependencies
        $sess = $this->getMockBuilder(\stdClass::class)->addMethods(['changeChecker'])->getMock();
        $sess->tribe = 1;
        $sess->uid = 1;
        $sess->gold = 10;
        $sess->goldclub = true;
        $sess->sit = 0;
        $sess->checker = '123';
        
        $vil = $this->getMockBuilder(\stdClass::class)->getMock();
        $vil->wid = 1;
        $vil->resarray = ['f19t' => 0];
        $vil->natar = 0;
        $vil->allcrop = 1000;
        $vil->pop = 10;
        $vil->master = 0;
        $vil->capital = 0;
        
        $log = $this->createMock(\stdClass::class);
        $tech = [];
        $gen = null;
        $bids = [];
        
        $building = new Building($db, $sess, $vil, $log, $tech, $gen, $bids);
        
        $this->assertInstanceOf(Building::class, $building);
    }

    public function testCanBuildWithDemolition()
    {
        $db = $this->createMock(\MYSQLi_DB::class);
        // Configure the mock database
        $db->method('getDemolition')->willReturn([['buildnumber' => 1]]);
        $db->method('getJobs')->willReturn([]);
        
        $sess = $this->getMockBuilder(\stdClass::class)->addMethods(['changeChecker'])->getMock();
        $sess->tribe = 1;
        
        $vil = $this->getMockBuilder(\stdClass::class)->getMock();
        $vil->wid = 1;
        $vil->resarray = ['f19t' => 0];
        
        $log = $this->createMock(\stdClass::class);
        $tech = [];
        $gen = null;
        $bids = [];
        
        $building = new Building($db, $sess, $vil, $log, $tech, $gen, $bids);
        
        $this->assertEquals(11, $building->canBuild(1, 1));
    }
}
