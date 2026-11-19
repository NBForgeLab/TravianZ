<?php
namespace Tests\Admin\Services;

use App\Admin\Services\CronSettingsService;
use App\Admin\Services\DebugLogService;
use App\Admin\Services\ExtraSettingsService;
use App\Admin\Services\GiveResBonusHandler;
use App\Admin\Services\MaintenanceBanService;
use App\Admin\Services\MaintenanceCleanBanDataService;
use App\Admin\Services\MaintenanceResetGoldService;
use App\Database\IDbConnection;
use PHPUnit\Framework\TestCase;

class MaintenanceAndMiscServicesTest extends TestCase
{
    public function testMaintenanceBanCleanAndResetGold()
    {
        $queries = [];
        $db = $this->createMock(IDbConnection::class);
        $db->method('escape')->willReturnArgument(0);
        $db->expects($this->exactly(6))
            ->method('query')
            ->willReturnCallback(function (string $sql) use (&$queries) {
                $queries[] = $sql;
                return true;
            });

        $ban = new MaintenanceBanService($db);
        $ban->massBan(12, '2026-01-01 00:00:00', 'Upgrade', 1);
        $this->assertStringContainsString('INSERT INTO ' . TB_PREFIX . 'banlist', $queries[0]);
        $this->assertStringContainsString('INSERT INTO ' . TB_PREFIX . 'admin_log', $queries[1]);

        $clean = new MaintenanceCleanBanDataService($db);
        $clean->cleanBanList(1);
        $this->assertStringContainsString('TRUNCATE TABLE ' . TB_PREFIX . 'banlist', $queries[2]);

        $reset = new MaintenanceResetGoldService($db);
        $reset->resetGoldForAllUsers(1);
        $this->assertStringContainsString('UPDATE ' . TB_PREFIX . 'users SET gold = 0', $queries[4]);
    }

    public function testGiveResBonusHandlerValidatesAndLogs()
    {
        $queries = [];
        $db = $this->createMock(IDbConnection::class);
        $db->method('escape')->willReturnArgument(0);
        $db->expects($this->exactly(2))
            ->method('query')
            ->willReturnCallback(function (string $sql) use (&$queries) {
                $queries[] = $sql;
                return true;
            });

        $handler = new GiveResBonusHandler($db);
        $this->assertFalse($handler->giveResBonus(0, 1, 1000));
        $this->assertTrue($handler->giveResBonus(50, 1, 1000));
        $this->assertStringContainsString('SET gold = gold + 50', $queries[0]);
        $this->assertStringContainsString('Gave 50 gold to all players', $queries[1]);
    }

    public function testDebugLogServiceClearsLogAndSavesSettings()
    {
        $logFile = tempnam(sys_get_temp_dir(), 'tz_dbg_');
        file_put_contents($logFile, 'old');
        file_put_contents($logFile . '.1', 'rotated');

        $db = $this->getMockBuilder(\MYSQLi_DB::class)->disableOriginalConstructor()->getMock();
        $db->expects($this->once())->method('setDebugSettings')->with(true, false, true, true, 10, 60);
        $db->expects($this->once())->method('setDebugMode')->with(1, 2);
        $db->expects($this->exactly(3))->method('query');

        $svc = new DebugLogService($db, $logFile);
        $svc->saveSettings(1, 0, 1, 1, 10, 60, 2);
        $svc->toggle(1, 2);
        $svc->clear(2);

        $this->assertSame('', file_get_contents($logFile));
        $this->assertFileDoesNotExist($logFile . '.1');
        @unlink($logFile);
    }

    public function testExtraSettingsAndCronMissingConstants()
    {
        $cfg = tempnam(sys_get_temp_dir(), 'tz_extra_');
        file_put_contents($cfg, "<?php\ndefine('AUTOMATION_LOCK_FILE_NAME', 'lock');\n?>");

        $db = $this->createMock(IDbConnection::class);
        $db->method('escape')->willReturnArgument(0);
        $db->expects($this->exactly(2))->method('query');

        $extra = new ExtraSettingsService($db, 'tz_', $cfg);
        $extra->updateExtraSettings(
            [
                'templateContents' => 'cfg=%A%',
                'values' => ['%A%' => 'ok'],
            ],
            function (&$txt, $k, $v) { $txt = str_replace($k, $v, $txt); },
            fn($txt) => $txt . "\ndefine('AUTOMATION_LOCK_FILE_NAME', 'lock');"
        );
        $extra->logAdminAction(1, 'Updated extra');
        $this->assertStringContainsString('cfg=ok', file_get_contents($cfg));

        $cron = new CronSettingsService($db, 'tz_', $cfg);
        $cron->updateCronSettings([
            'loop' => 300,
            'tick' => 60,
            'cronKey' => "k'1",
            'cleanReports' => 14,
            'cleanChat' => 7,
            'cleanMessages' => 0,
        ]);
        $cron->logAdminAction(1, 'Updated cron');
        $content = file_get_contents($cfg);
        $this->assertStringContainsString("define('CRON_KEY', 'k\\'1');", $content);
        $this->assertStringContainsString("define('CRON_LOOP_SECONDS', 300);", $content);

        @unlink($cfg . '.bak');
        @unlink($cfg);
    }
}
