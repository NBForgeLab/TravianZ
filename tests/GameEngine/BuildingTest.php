<?php
require_once __DIR__ . '/../DatabaseTestCase.php';
use Tests\DatabaseTestCase;

class BuildingTest extends DatabaseTestCase
{
    private $db;
    private $sess;
    private $vil;
    private $log;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = $GLOBALS['database'];
        
        // Setup session
        $this->sess = new class {
            public $checker = '123';
            public $tribe = 1;
            public $uid = 1;
            public $gold = 10;
            public $goldclub = true;
            public $sit = 0;
            public function changeChecker() {}
        };
        
        // Setup village
        $this->vil = (object) ['wid' => 1, 'resarray' => ['f19t' => 0, 'f19' => 0], 'natar' => 0, 'allcrop' => 1000, 'pop' => 10, 'master' => 0, 'capital' => 0];
        $this->log = $this->createMock(stdClass::class);
        
        // Setup dummy building data
        $GLOBALS['bid19'] = [1 => ['wood' => 10, 'clay' => 10, 'iron' => 10, 'crop' => 10, 'pop' => 1, 'time' => 10]];
    }

    public function testAddBuildingSuccessfully()
    {
        $building = new Building($this->db, $this->sess, $this->vil, $this->log);
        
        // Simulate constructing a new building (gid 19, field 19)
        $get = ['a' => 19, 'id' => 19, 'c' => '123'];
        
        $building->procBuild($get);
        
        // Verify in DB
        $jobs = $this->db->getJobs($this->vil->wid);
        $this->assertGreaterThan(0, count($jobs));
        $this->assertEquals(19, $jobs[0]['type']);
    }
}
