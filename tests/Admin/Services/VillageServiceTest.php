<?php
namespace Tests\Admin\Services;

use App\Admin\Services\VillageService;
use App\Database\IDbConnection;
use PHPUnit\Framework\TestCase;

class VillageServiceTest extends TestCase
{
    private $villageService;
    private $dbMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbMock = $this->createMock(IDbConnection::class);
        $this->villageService = new VillageService($this->dbMock);
    }

    public function testUpdateVillageBuildings()
    {
        $buildings = [];
        for ($i = 1; $i <= 40; $i++) {
            $buildings["id{$i}level"] = 1;
            $buildings["id{$i}gid"] = 2;
        }

        $queries = [];
        $this->dbMock->expects($this->exactly(2))
            ->method('query')
            ->willReturnCallback(function (string $sql) use (&$queries) {
                $queries[] = $sql;
                return true;
            });

        $this->dbMock->expects($this->once())
            ->method('escape')
            ->willReturnArgument(0);

        $this->villageService->updateVillageBuildings(123, $buildings, 10);

        $this->assertStringContainsString('UPDATE ' . TB_PREFIX . 'fdata SET', $queries[0]);
        $this->assertStringContainsString('INSERT INTO ' . TB_PREFIX . 'admin_log', $queries[1]);
    }

    public function testRenameVillage()
    {
        $queries = [];
        $this->dbMock->expects($this->exactly(2))
            ->method('query')
            ->willReturnCallback(function (string $sql) use (&$queries) {
                $queries[] = $sql;
                return true;
            });

        $this->dbMock->method('escape')->willReturnArgument(0);

        $this->villageService->renameVillage(123, 'New Village Name', 10);

        $this->assertStringContainsString("UPDATE " . TB_PREFIX . "vdata SET name = 'New Village Name' WHERE wref = 123", $queries[0]);
        $this->assertStringContainsString('INSERT INTO ' . TB_PREFIX . 'admin_log', $queries[1]);
    }
}
