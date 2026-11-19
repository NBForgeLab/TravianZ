<?php
namespace Tests\Admin\Services;

use App\Admin\Services\AllianceAdminService;
use App\Database\IDbConnection;
use PHPUnit\Framework\TestCase;

class AllianceAdminServiceTest extends TestCase
{
    public function testDeleteAlliance()
    {
        $queries = [];
        $db = $this->createMock(IDbConnection::class);
        $db->expects($this->exactly(9))
            ->method('query')
            ->willReturnCallback(function (string $sql) use (&$queries) {
                $queries[] = $sql;
                return true;
            });

        $service = new AllianceAdminService($db, 'tb_');
        $service->deleteAlliance(5);

        $this->assertStringContainsString('UPDATE tb_users SET alliance = 0 WHERE alliance = 5', $queries[0]);
        $this->assertStringContainsString('DELETE FROM tb_alidata WHERE id = 5', $queries[1]);
        $this->assertStringContainsString('DELETE FROM tb_forum_cat WHERE alliance = 5', $queries[8]);
    }

    public function testDeleteAllianceMedals()
    {
        $db = $this->createMock(IDbConnection::class);
        $db->expects($this->exactly(3))->method('query');
        $db->method('affected_rows')->willReturn(3);

        $service = new AllianceAdminService($db, 'tb_');
        $service->deleteAllianceMedal(1, 2);
        $this->assertEquals(3, $service->deleteAllianceMedalsByAllyId(2));
        $this->assertEquals(3, $service->deleteAllianceMedalsByWeek(10));
    }
}
