<?php
require_once __DIR__ . '/../../GameEngine/Building.php';
require_once __DIR__ . '/../../GameEngine/Database.php';
use PHPUnit\Framework\TestCase;

class BuildingTest extends TestCase
{
    private $mockDb;
    private $sess;
    private $vil;
    private $log;

    protected function setUp(): void
    {
        // Mock the concrete MYSQLi_DB class
        $this->mockDb = $this->getMockBuilder(\MYSQLi_DB::class)
                             ->disableOriginalConstructor()
                             ->onlyMethods(['getJobs'])
                             ->getMock();
        
        // Setup session
        $this->sess = new class {
            public $checker = '123';
            public $tribe = 1;
            public $uid = 1;
            public $gold = 10;
            public $goldclub = true;
            public $sit = 0;
            public function changeChecker() {}
            public function sitterCan($perm) { return true; }
        };
        
        // Setup village
        $this->vil = (object) ['wid' => 1, 'resarray' => ['f19t' => 0, 'f19' => 0], 'natar' => 0, 'allcrop' => 1000, 'pop' => 10, 'master' => 0, 'capital' => 0];
        $this->log = $this->createMock(stdClass::class);
        
        // Setup dummy building data
        $GLOBALS['bid19'] = [1 => ['wood' => 10, 'clay' => 10, 'iron' => 10, 'crop' => 10, 'pop' => 1, 'time' => 10]];
    }

    public function testDependencyInjectionWithMockDatabase()
    {
        // Configure the mock
        $this->mockDb->expects($this->once())
                     ->method('getJobs')
                     ->with($this->equalTo(1))
                     ->willReturn([]);
        
        // Inject mock - constructor calls loadBuilding() which calls getJobs()
        // If it uses the injected mock, this will pass.
        $building = new Building($this->mockDb, $this->sess, $this->vil, $this->log, [], null, []);
        
        // If we reach here, the mock was used and the expectation was met
        $this->assertTrue(true);
    }
}
