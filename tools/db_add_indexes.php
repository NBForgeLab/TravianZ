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
$db->set_charset('utf8');

function tableExists(mysqli $db, string $table): bool
{
    $t = $db->real_escape_string($table);
    $res = $db->query("SHOW TABLES LIKE '{$t}'");
    if (!$res) return false;
    $row = $res->fetch_row();
    return is_array($row) && isset($row[0]) && $row[0] === $table;
}

function indexExists(mysqli $db, string $table, string $indexName): bool
{
    $t = str_replace('`', '``', $table);
    $res = $db->query("SHOW INDEX FROM `{$t}`");
    if (!$res) return false;
    while ($row = $res->fetch_assoc()) {
        if (($row['Key_name'] ?? '') === $indexName) {
            return true;
        }
    }
    return false;
}

function addIndex(mysqli $db, string $table, string $indexName, string $columnsSql): bool
{
    $t = str_replace('`', '``', $table);
    $i = str_replace('`', '``', $indexName);
    return (bool) $db->query("ALTER TABLE `{$t}` ADD INDEX `{$i}` ({$columnsSql})");
}

$targets = [
    [
        'table' => $prefix . 'wdata',
        'index' => 'fieldtype-x-y',
        'cols' => '`fieldtype`,`x`,`y`',
    ],
    [
        'table' => $prefix . 'odata',
        'index' => 'conqured-type',
        'cols' => '`conqured`,`type`',
    ],
    [
        'table' => $prefix . 'movement',
        'index' => 'proc-sort_type-ref-endtime',
        'cols' => '`proc`,`sort_type`,`ref`,`endtime`',
    ],
    [
        'table' => $prefix . 'bdata',
        'index' => 'master-timestamp',
        'cols' => '`master`,`timestamp`',
    ],
];

$changed = 0;
foreach ($targets as $t) {
    $table = $t['table'];
    $index = $t['index'];
    $cols = $t['cols'];

    if (!tableExists($db, $table)) {
        fwrite(STDERR, "SKIP: missing table {$table}\n");
        continue;
    }

    if (indexExists($db, $table, $index)) {
        echo "OK: {$table} {$index} already exists\n";
        continue;
    }

    if (!addIndex($db, $table, $index, $cols)) {
        fwrite(STDERR, "FAIL: add index {$index} on {$table}\n");
        fwrite(STDERR, $db->error . "\n");
        exit(1);
    }

    $changed++;
    echo "OK: added {$table} {$index}\n";
}

echo "DONE: {$changed} indexes added\n";
exit(0);
