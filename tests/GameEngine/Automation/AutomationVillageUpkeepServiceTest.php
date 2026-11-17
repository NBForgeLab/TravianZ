<?php
namespace Tests\GameEngine\Automation;

require_once __DIR__ . '/../../../GameEngine/Automation/AutomationDataProvider.php';
require_once __DIR__ . '/../../../GameEngine/Automation/AutomationVillageUpkeepService.php';
require_once __DIR__ . '/../../../GameEngine/Database.php';
require_once __DIR__ . '/../../../GameEngine/Building.php';
require_once __DIR__ . '/../../DatabaseTestCase.php';

use Tests\DatabaseTestCase;
use App\Automation\AutomationVillageUpkeepService;
use App\Automation\AutomationDataProvider;

class AutomationVillageUpkeepServiceTest extends DatabaseTestCase
{
    private $service;
    private $mockDatabase;
    private $mockDataProvider;
    private $mockDependencyProvider;

    protected function setUp(): void
    {
        parent::setUp();
        // Mock the class MYSQLi_DB, not the interface IDbConnection
        $this->mockDatabase = $this->getMockBuilder(\MYSQLi_DB::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();
        $this->mockDataProvider = $this->createMock(AutomationDataProvider::class);
        $this->mockDependencyProvider = $this->getMockBuilder(\stdClass::class)
                                     ->addMethods(['procClimbers', 'bountysortOasis', 'getAllUnits', 'bountyGetResourceProd'])
                                     ->getMock();
        // Set up the mock database with a reachable dblink property
        $this->mockDatabase->dblink = new \mysqli();
        
        $this->service = new AutomationVillageUpkeepService($this->mockDatabase, $this->mockDataProvider, $this->mockDependencyProvider);
    }

    public function testRecountPop()
    {
        $vid = 1;
        $this->mockDatabase->expects($this->atLeastOnce())
            ->method('getResourceLevel')
            ->willReturnMap([
                [$vid, true, ['f1' => 1, 'f1t' => 1]],
                [$vid, false, ['f1' => 1, 'f1t' => 1]]
            ]);
        
        $this->mockDataProvider->expects($this->once())
            ->method('getBid')
            ->with(1)
            ->willReturn([0 => ['pop' => 0], 1 => ['pop' => 1]]);
        
        $this->mockDatabase->method('getVillageField')->willReturn(1);
        // Building::recountCP expects a database parameter, might be triggered and call getResourceLevel again.
        // Assuming Building::recountCP doesn't need to call getResourceLevel or we can mock it out if needed.
        
        $pop = $this->service->recountPop($vid);
        $this->assertEquals(1, $pop);
    }

    public function testGetTypeLevel()
    {
        $vid = 1;
        $tid = 1;
        $this->mockDatabase->expects($this->once())
            ->method('getResourceLevel')
            ->with($vid)
            ->willReturn(['f1' => 5, 'f1t' => 1]);
        
        $level = $this->service->getTypeLevel($tid, $vid);
        $this->assertEquals(5, $level);
    }
}
