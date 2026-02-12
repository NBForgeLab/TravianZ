<?php
declare(strict_types=1);

use App\Utils\AccessLogger;
use PHPUnit\Framework\TestCase;

final class AccessLoggerTest extends TestCase
{
    private string $logFile;

    protected function setUp(): void
    {
        $dir = __DIR__ . '/../../var/log';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $fname = defined('PAGE_ACCESS_LOG_FILENAME') ? constant('PAGE_ACCESS_LOG_FILENAME') : 'access.log';
        $this->logFile = $dir . '/' . $fname;
        if (file_exists($this->logFile)) {
            @unlink($this->logFile);
        }
        if (!defined('LOG_PAGE_ACCESS')) {
            define('LOG_PAGE_ACCESS', true);
        }
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['PHP_SELF'] = '/index.php';
        $_COOKIE = ['phpsessid' => 'abc', 'token' => 'secret', 'name' => 'john'];
        $_GET = ['q' => 'search', 'pwd' => 'xxx'];
        $_POST = ['password' => 'p@ss', 'param' => 'value'];
    }

    protected function tearDown(): void
    {
        if (file_exists($this->logFile)) {
            @unlink($this->logFile);
        }
    }

    public function testLogRequestWritesFile(): void
    {
        $ok = AccessLogger::logRequest();
        $this->assertTrue($ok);
        $this->assertFileExists($this->logFile);
        $content = (string) file_get_contents($this->logFile);
        $this->assertStringContainsString('127.0.0.1', $content);
        $this->assertStringContainsString('/index.php', $content);
        $this->assertStringContainsString('phpsessid=[REDACTED]', $content);
        $this->assertStringContainsString('token=[REDACTED]', $content);
        $this->assertStringContainsString('name=john', $content);
        $this->assertStringContainsString('?q=search', $content);
        $this->assertStringContainsString('pwd=[REDACTED]', $content);
        $this->assertStringContainsString('[POSTDATA]', $content);
        $this->assertStringContainsString('password=[REDACTED]', $content);
        $this->assertStringContainsString('param=value', $content);
    }
}
