<?php
namespace Tests\Admin\Services;

use App\Admin\Services\SystemMessageService;
use PHPUnit\Framework\TestCase;

class SystemMessageServiceTest extends TestCase
{
    private $sysMsgService;
    private $dbMock;
    private $testDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbMock = $this->createMock(\MYSQLi_DB::class);
        $this->testDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tz_sysmsg_' . uniqid('', true) . DIRECTORY_SEPARATOR;
        mkdir($this->testDir . 'Templates', 0777, true);

        $this->dbMock->expects($this->once())
            ->method('setUsersOk')
            ->with(1);

        $this->sysMsgService = new SystemMessageService($this->dbMock, $this->testDir);

        file_put_contents($this->testDir . 'Templates/text_format.tpl', '<?php $txt = "%TEKST%";');
    }

    protected function tearDown(): void
    {
        @unlink($this->testDir . 'Templates/text_format.tpl');
        @unlink($this->testDir . 'Templates/text.tpl');
        @rmdir($this->testDir . 'Templates');
        @rmdir($this->testDir);
        parent::tearDown();
    }

    public function testPostSystemMessage()
    {
        $this->sysMsgService->postSystemMessage('Test Subject', 'Test Message', 'red');

        $outFile = $this->testDir . 'Templates/text.tpl';
        $this->assertFileExists($outFile);
        $content = file_get_contents($outFile);
        $this->assertStringContainsString('Test Subject', $content);
        $this->assertStringContainsString('Test Message', $content);
        $this->assertStringContainsString('red', $content);
    }

    public function testPostSystemMessagePreventsPhpCodeInjectionAndBackreferenceCorruption()
    {
        $subject = 'Hi \"; phpinfo(); //';
        $message = 'Cost: $100 $0 $1 \\ "quote"';
        $this->sysMsgService->postSystemMessage($subject, $message, 'blue');

        $outFile = $this->testDir . 'Templates/text.tpl';
        $this->assertFileExists($outFile);

        $txt = null;
        include $outFile;
        $this->assertIsString($txt);
        $this->assertStringContainsString($subject, $txt);
        $this->assertStringContainsString('Cost: $100 $0 $1 \\ "quote"', $txt);
    }
}
