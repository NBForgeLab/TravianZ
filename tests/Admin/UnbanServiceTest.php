<?php
namespace Tests\Admin;

use App\Admin\Services\UnbanService;
use App\Database\IDbConnection;
use PHPUnit\Framework\TestCase;

class UnbanServiceTest extends TestCase
{
    public function testUnbanByReasonUpdatesDatabase()
    {
        $queries = [];
        $database = $this->createMock(IDbConnection::class);
        $database->expects($this->exactly(2))
            ->method('query')
            ->willReturnCallback(function (string $sql) use (&$queries) {
                $queries[] = $sql;
                return true;
            });
        $database->method('escape')->willReturnArgument(0);

        $service = new UnbanService($database);
        $service->unbanByReason('some_reason', 1);

        $this->assertStringContainsString('UPDATE ' . TB_PREFIX . 'banlist', $queries[0]);
        $this->assertStringContainsString('SET active = 0', $queries[0]);
        $this->assertStringContainsString("reason = 'some_reason'", $queries[0]);
        $this->assertStringContainsString('INSERT INTO ' . TB_PREFIX . 'admin_log', $queries[1]);
    }
}
