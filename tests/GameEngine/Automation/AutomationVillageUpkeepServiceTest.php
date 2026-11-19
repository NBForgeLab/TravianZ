<?php
namespace Tests\GameEngine\Automation;

require_once __DIR__ . '/../../../GameEngine/Database.php';
require_once __DIR__ . '/../../../GameEngine/Building.php';

use PHPUnit\Framework\TestCase;
use App\Automation\AutomationVillageUpkeepService;
use App\Automation\AutomationDataProvider;

class AutomationVillageUpkeepServiceTest extends TestCase
{
    private $service;
    private $mockDatabase;
    private $mockDataProvider;
    private $mockDependencyProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockDatabase = $this->getMockBuilder(\MYSQLi_DB::class)
                                   ->disableOriginalConstructor()
                                   ->getMock();
        $this->mockDataProvider = $this->createMock(AutomationDataProvider::class);
        $this->mockDependencyProvider = new class {
            public function procClimbers($owner) {}
            public function bountysortOasis($id) {}
            public function getAllUnits($id) { return []; }
            public function bountyGetResourceProd($id) { return []; }
        };
        
        $this->service = new AutomationVillageUpkeepService($this->mockDatabase, $this->mockDataProvider, $this->mockDependencyProvider);
    }

    public function testRecountPop()
    {
        $vid = 1;
        $resRow = [];
        for ($i = 1; $i <= 40; $i++) {
            $resRow['f' . $i] = ($i === 1) ? 1 : 0;
            $resRow['f' . $i . 't'] = ($i === 1) ? 1 : 0;
        }
        $this->mockDatabase->expects($this->atLeastOnce())
            ->method('getResourceLevel')
            ->willReturn($resRow);
        
        $this->mockDataProvider->expects($this->once())
            ->method('getBid')
            ->with(1)
            ->willReturn([0 => ['pop' => 0], 1 => ['pop' => 1]]);
        
        $this->mockDatabase->method('getVillageField')->willReturn(1);
        
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
