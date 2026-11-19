<?php
namespace Tests\Admin;

use App\Admin\Services\ResetPlusService;
use App\Database\IDbConnection;
use PHPUnit\Framework\TestCase;

class ResetPlusServiceTest extends TestCase
{
    public function testResetAllPlusUpdatesDatabase()
    {
        $queries = [];
        $database = $this->createMock(IDbConnection::class);
        $database->expects($this->exactly(2))
            ->method('query')
            ->willReturnCallback(function (string $sql) use (&$queries) {
                $queries[] = $sql;
                return true;
            });
        $database->expects($this->once())
            ->method('escape')
            ->willReturn('Reset Plus to 0 for all users');

        $service = new ResetPlusService($database);
        $service->resetAllPlus(1);

        $this->assertStringContainsString('UPDATE ' . TB_PREFIX . 'users SET plus = 0', $queries[0]);
        $this->assertStringContainsString('INSERT INTO ' . TB_PREFIX . 'admin_log', $queries[1]);
    }
}
