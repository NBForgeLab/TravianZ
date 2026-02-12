<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);

require $root . '/GameEngine/config.php';

if (!extension_loaded('mysqli')) {
    fwrite(STDERR, "FAIL: mysqli extension is not loaded\n");
    exit(1);
}

$mysqli = @mysqli_connect(SQL_SERVER, SQL_USER, SQL_PASS, SQL_DB);
if (!$mysqli) {
    fwrite(STDERR, "FAIL: could not connect to database\n");
    exit(1);
}

$expectedTables = [
    TB_PREFIX . 'users',
    TB_PREFIX . 'wdata',
];

foreach ($expectedTables as $table) {
    $escaped = mysqli_real_escape_string($mysqli, $table);
    $res = mysqli_query($mysqli, "SHOW TABLES LIKE '{$escaped}'");
    if (!$res) {
        fwrite(STDERR, "FAIL: SHOW TABLES failed for {$table}\n");
        exit(1);
    }
    if (mysqli_num_rows($res) < 1) {
        fwrite(STDERR, "FAIL: missing table {$table}\n");
        exit(1);
    }
}

$wdataTable = TB_PREFIX . 'wdata';
$res = mysqli_query($mysqli, "SELECT COUNT(*) AS c FROM {$wdataTable}");
if (!$res) {
    fwrite(STDERR, "FAIL: could not count {$wdataTable}\n");
    exit(1);
}
$row = mysqli_fetch_assoc($res);
$count = (int) ($row['c'] ?? 0);
if ($count <= 0) {
    fwrite(STDERR, "FAIL: {$wdataTable} is empty\n");
    exit(1);
}

echo "OK: DB connected and schema looks ready (wdata={$count})\n";
exit(0);

