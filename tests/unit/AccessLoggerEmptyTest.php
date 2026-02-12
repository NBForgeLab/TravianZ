<?php
declare(strict_types=1);

use App\Utils\AccessLogger;
use PHPUnit\Framework\TestCase;

final class AccessLoggerEmptyTest extends TestCase
{
    private string $logFile;

    protected function setUp(): void
    {
        $dir = __DIR__ . '/../../var/log';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $this->logFile = $dir . '/empty-access.log';
        if (file_exists($this->logFile)) {
            @unlink($this->logFile);
        }
        if (!defined('LOG_PAGE_ACCESS')) {
            define('LOG_PAGE_ACCESS', true);
        }
        if (!defined('PAGE_ACCESS_LOG_FILENAME')) {
            define('PAGE_ACCESS_LOG_FILENAME', 'empty-access.log');
        }
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['PHP_SELF'] = '/index.php';
        $_COOKIE = [];
        $_GET = [];
        $_POST = [];
    }

    protected function tearDown(): void
    {
        if (file_exists($this->logFile)) {
            @unlink($this->logFile);
        }
    }

    public function testLogRequestWithEmptyGlobals(): void
    {
        $ok = AccessLogger::logRequest();
        $this->assertTrue($ok);
        $this->assertFileExists($this->logFile);
        $content = (string) file_get_contents($this->logFile);
        $this->assertStringContainsString('127.0.0.1', $content);
        $this->assertStringContainsString('/index.php', $content);
        $this->assertStringNotContainsString('[POSTDATA]', $content);
        $this->assertStringNotContainsString('?', $content);
    }
}
