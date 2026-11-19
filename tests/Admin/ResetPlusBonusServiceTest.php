<?php
namespace Tests\Admin;

use App\Admin\Services\ResetPlusBonusService;
use App\Database\IDbConnection;
use PHPUnit\Framework\TestCase;

class ResetPlusBonusServiceTest extends TestCase
{
    public function testResetAllPlusBonusUpdatesDatabase()
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
            ->willReturn('Reset resource bonuses (b1-b4) to 0 for all users');

        $service = new ResetPlusBonusService($database);
        $service->resetAllPlusBonus(1);

        $this->assertStringContainsString('UPDATE ' . TB_PREFIX . 'users SET b1 = 0', $queries[0]);
        $this->assertStringContainsString('INSERT INTO ' . TB_PREFIX . 'admin_log', $queries[1]);
    }
}
