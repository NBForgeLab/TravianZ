<?php
namespace Tests\Admin\Services;

use App\Admin\Services\MedalService;
use App\Database\IDbConnection;
use PHPUnit\Framework\TestCase;

class MedalServiceTest extends TestCase
{
    private $medalService;
    private $dbMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbMock = $this->createMock(IDbConnection::class);
        $this->medalService = new MedalService($this->dbMock);
    }

    public function testDeleteMedal()
    {
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

        $this->medalService->deleteMedal(1, 10, 20, 'testuser');

        $this->assertStringContainsString('UPDATE ' . TB_PREFIX . 'medal SET del = 1 WHERE id = 1', $queries[0]);
        $this->assertStringContainsString('INSERT INTO ' . TB_PREFIX . 'admin_log', $queries[1]);
    }
}
