<?php
declare(strict_types=1);

use App\Utils\AccessLogger;
use PHPUnit\Framework\TestCase;

final class AccessLoggerFailureTest extends TestCase
{
    private string $logFile;

    /**
     * @runInSeparateProcess
     */
    public function testLogRequestFailureReturnsFalseAndNoFile(): void
    {
        $dir = __DIR__ . '/../../var/log';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        // nested directory that doesn't exist - writing should fail
        $this->logFile = $dir . '/fail/access.log';
        if (file_exists($this->logFile)) {
            @unlink($this->logFile);
        }
        define('LOG_PAGE_ACCESS', true);
        define('PAGE_ACCESS_LOG_FILENAME', 'fail/access.log');
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['PHP_SELF'] = '/index.php';
        $_COOKIE = [];
        $_GET = [];
        $_POST = [];

        $ok = AccessLogger::logRequest();
        $this->assertFalse($ok);
        $this->assertFileDoesNotExist($this->logFile);
    }
}
