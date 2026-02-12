<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$configPath = $root . '/GameEngine/config.php';
if (file_exists($configPath)) {
    require_once $configPath;
}

$prefix = getenv('TRAVIANZ_TB_PREFIX');
if (($prefix === false || $prefix === '') && defined('TB_PREFIX')) {
    $prefix = TB_PREFIX;
}
if ($prefix === false || $prefix === '') {
    fwrite(STDERR, "FAIL: missing table prefix (TRAVIANZ_TB_PREFIX or TB_PREFIX)\n");
    exit(2);
}

if (!defined('SQL_SERVER') || !defined('SQL_USER') || !defined('SQL_DB')) {
    fwrite(STDERR, "FAIL: missing DB config constants\n");
    exit(2);
}

require_once $root . '/GameEngine/Database.php';
$port = defined('SQL_PORT') ? (int) SQL_PORT : 3306;
$database = new MYSQLi_DB(SQL_SERVER, SQL_USER, defined('SQL_PASS') ? SQL_PASS : '', SQL_DB, $port);
$db = $database->return_link();
$db->set_charset('utf8mb4');

function tables(mysqli $db, string $prefix): array
{
    $p = $db->real_escape_string($prefix);
    $res = $db->query("SHOW TABLES LIKE '{$p}%'");
    if (!$res) return [];
    $out = [];
    while ($row = $res->fetch_row()) {
        if (isset($row[0])) $out[] = $row[0];
    }
    return $out;
}

function tableCharset(mysqli $db, string $table): ?string
{
    $t = str_replace('`', '``', $table);
    $res = $db->query("SHOW TABLE STATUS LIKE '{$t}'");
    if (!$res) return null;
    $row = $res->fetch_assoc();
    return $row['Collation'] ?? null;
}

function migrateTable(mysqli $db, string $table): bool
{
    $t = str_replace('`', '``', $table);
    return (bool) $db->query("ALTER TABLE `{$t}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
}

$changed = 0;
$list = tables($db, $prefix);
foreach ($list as $t) {
    $coll = tableCharset($db, $t);
    if ($coll === null) {
        fwrite(STDERR, "SKIP: unknown charset for {$t}\n");
        continue;
    }
    if (stripos($coll, 'utf8mb4') !== false) {
        echo "OK: {$t} already utf8mb4 ({$coll})\n";
        continue;
    }
    if (!migrateTable($db, $t)) {
        fwrite(STDERR, "FAIL: migrate {$t} to utf8mb4\n");
        fwrite(STDERR, $db->error . "\n");
        exit(1);
    }
    $changed++;
    echo "OK: migrated {$t} to utf8mb4\n";
}

echo "DONE: {$changed} tables migrated to utf8mb4\n";
exit(0);
