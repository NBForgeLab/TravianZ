<?php
namespace Tests\Admin\Services;

use App\Admin\Services\CronSettingsService;
use App\Database\IDbConnection;
use PHPUnit\Framework\TestCase;

class CronSettingsServiceTest extends TestCase
{
    private $service;
    private $configPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configPath = tempnam(sys_get_temp_dir(), 'tz_cron_cfg_');
        file_put_contents(
            $this->configPath,
            "<?php\n"
            . "define('CRON_LOOP_SECONDS', 300);\n"
            . "define('CRON_TICK_SECONDS', 60);\n"
            . "define('CRON_KEY', 'oldKey');\n"
            . "define('CLEANUP_REPORTS_DAYS', 14);\n"
            . "define('CLEANUP_CHAT_DAYS', 7);\n"
            . "define('CLEANUP_MESSAGES_DAYS', 0);\n"
            . "define('AUTOMATION_LOCK_FILE_NAME', 'lock');\n"
            . "?>"
        );
        $db = $this->createMock(IDbConnection::class);
        $this->service = new CronSettingsService($db, 'tz_', $this->configPath);
    }

    protected function tearDown(): void
    {
        if ($this->configPath && file_exists($this->configPath)) {
            @unlink($this->configPath);
        }
        parent::tearDown();
    }

    public function testUpdateCronSettings()
    {
        $settings = [
            'loop' => 600,
            'tick' => 120,
            'cronKey' => 'newKey',
            'cleanReports' => 30,
            'cleanChat' => 15,
            'cleanMessages' => 5,
        ];

        $this->service->updateCronSettings($settings);

        $content = file_get_contents($this->configPath);
        $this->assertStringContainsString("define('CRON_LOOP_SECONDS', 600);", $content);
        $this->assertStringContainsString("define('CRON_TICK_SECONDS', 120);", $content);
        $this->assertStringContainsString("define('CRON_KEY', 'newKey');", $content);
    }
}
