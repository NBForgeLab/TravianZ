<?php

declare(strict_types=1);

namespace TravianZ\Tests\AdminE2E;

use mysqli;
use RuntimeException;

function runner(): TestRunner
{
    global $runner;
    if (!$runner instanceof TestRunner) {
        throw new RuntimeException('Test runner not initialized.');
    }
    return $runner;
}

function assertTrue(bool $cond, string $message = 'Assertion failed'): void
{
    if (!$cond) {
        throw new RuntimeException($message);
    }
}

function assertContains(string $needle, string $haystack, string $message = ''): void
{
    if (!str_contains($haystack, $needle)) {
        $suffix = $message !== '' ? " ({$message})" : '';
        throw new RuntimeException("Expected to find '{$needle}'{$suffix}");
    }
}

function httpGet(string $url, int $timeoutSeconds = 10): array
{
    $headers = [];
    $body = '';
    $status = 0;

    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT => $timeoutSeconds,
            CURLOPT_HEADER => true,
        ]);
        $raw = curl_exec($ch);
        if (!is_string($raw)) {
            $err = curl_error($ch);
            unset($ch);
            throw new RuntimeException('HTTP request failed: ' . $err);
        }
        $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        unset($ch);

        $rawHeaders = substr($raw, 0, $headerSize);
        $body = substr($raw, $headerSize);
        $headers = preg_split('/\r\n|\r|\n/', trim((string) $rawHeaders)) ?: [];
    } else {
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => $timeoutSeconds,
                'ignore_errors' => true,
            ],
        ]);
        $body = (string) file_get_contents($url, false, $ctx);
        $headers = $http_response_header ?? [];
        if (isset($headers[0]) && preg_match('#HTTP/\d+\.\d+\s+(\d+)#', $headers[0], $m)) {
            $status = (int) $m[1];
        }
    }

    return ['status' => $status, 'headers' => $headers, 'body' => $body];
}

final class ConfigSnapshot
{
    private string $path;
    private string $content;

    public function __construct(string $path)
    {
        $this->path = $path;
        $this->content = file_get_contents($path);
        if ($this->content === false) {
            throw new RuntimeException("Failed reading file: {$path}");
        }
    }

    public function restore(): void
    {
        $written = file_put_contents($this->path, $this->content);
        if ($written === false) {
            throw new RuntimeException("Failed restoring file: {$this->path}");
        }
    }
}

final class TestAdminUser
{
    public int $id;
    public string $username;
    public string $password;

    public function __construct(int $id, string $username, string $password)
    {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
    }
}

function ensureTestAdminUser(mysqli $db): TestAdminUser
{
    $username = 'e2e_admin';
    $password = bin2hex(random_bytes(12));
    $email = 'e2e_admin@travianz.test';

    $db->query("DELETE FROM `" . TB_PREFIX . "users` WHERE username='" . $db->real_escape_string($username) . "'");

    $hash = \trz_password_hash($password);
    if (!is_string($hash) || $hash === '') {
        throw new RuntimeException('Failed generating password hash.');
    }

    $stmt = $db->prepare(
        "INSERT INTO `" . TB_PREFIX . "users` (username,password,email,tribe,access,is_bcrypt,timestamp) VALUES (?,?,?,?,?,?,?)"
    );
    if (!$stmt) {
        throw new RuntimeException('Prepare failed: ' . $db->error);
    }

    $tribe = 1;
    $access = 9;
    $isBcrypt = 1;
    $timestamp = time();

    $stmt->bind_param('sssiiii', $username, $hash, $email, $tribe, $access, $isBcrypt, $timestamp);
    $ok = $stmt->execute();
    $stmt->close();

    if (!$ok) {
        throw new RuntimeException('Failed creating test admin user.');
    }

    $id = (int) $db->insert_id;
    if ($id <= 0) {
        $res = $db->query("SELECT id FROM `" . TB_PREFIX . "users` WHERE username='" . $db->real_escape_string($username) . "' LIMIT 1");
        $row = $res ? $res->fetch_assoc() : null;
        $id = (int) ($row['id'] ?? 0);
    }

    if ($id <= 0) {
        throw new RuntimeException('Failed retrieving test admin user id.');
    }

    return new TestAdminUser($id, $username, $password);
}

function cleanupTestAdminUser(mysqli $db, TestAdminUser $user): void
{
    $db->query("DELETE FROM `" . TB_PREFIX . "admin_log` WHERE `user`='" . $db->real_escape_string((string) $user->id) . "'");
    $db->query("DELETE FROM `" . TB_PREFIX . "admin_log` WHERE `log` LIKE '%" . $db->real_escape_string($user->username) . "%'");
    $db->query("DELETE FROM `" . TB_PREFIX . "users` WHERE id=" . (int) $user->id);
}

function runAdminMod(string $absolutePath, array $post, array $session): array
{
    $cwd = getcwd() ?: ROOT_DIR;
    $prevPost = $_POST ?? [];
    $prevSession = $_SESSION ?? [];

    $_POST = $post;

    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $_SESSION = $session;

    ob_start();
    $error = null;
    try {
        include $absolutePath;
    } catch (\Throwable $e) {
        $error = $e;
    }
    $output = (string) ob_get_clean();

    chdir($cwd);
    $_POST = $prevPost;
    $_SESSION = $prevSession;

    if ($error) {
        throw $error;
    }

    return ['output' => $output];
}
