<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);

$configPath = $root . '/GameEngine/config.php';
if (file_exists($configPath)) {
    require_once $configPath;
}

$action = $argv[1] ?? '';
if ($action === '') {
    fwrite(STDERR, "FAIL: missing action\n");
    exit(2);
}

function env(string $name, ?string $default = null): ?string {
    $v = getenv($name);
    if ($v === false || $v === '') return $default;
    return $v;
}

function connect(bool $useDb = true, bool $admin = false): mysqli {
    $host = env('TRAVIANZ_SQL_SERVER', defined('SQL_SERVER') ? SQL_SERVER : 'localhost');
    $port = (int) env('TRAVIANZ_SQL_PORT', defined('SQL_PORT') ? (string) SQL_PORT : '3306');
    $user = $admin
        ? env('TRAVIANZ_ADMIN_SQL_USER', env('TRAVIANZ_SQL_USER', defined('SQL_USER') ? SQL_USER : 'root'))
        : env('TRAVIANZ_SQL_USER', defined('SQL_USER') ? SQL_USER : 'root');
    $pass = $admin
        ? env('TRAVIANZ_ADMIN_SQL_PASS', env('TRAVIANZ_SQL_PASS', defined('SQL_PASS') ? SQL_PASS : ''))
        : env('TRAVIANZ_SQL_PASS', defined('SQL_PASS') ? SQL_PASS : '');
    $db   = env('TRAVIANZ_SQL_DB', defined('SQL_DB') ? SQL_DB : null);

    $mysqli = mysqli_init();
    $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);

    if ($useDb) {
        if ($db === null || $db === '') {
            fwrite(STDERR, "FAIL: missing db name\n");
            exit(2);
        }
        if (!$mysqli->real_connect($host, $user, $pass, $db, $port)) {
            fwrite(STDERR, "FAIL: connect db\n");
            exit(1);
        }
    } else {
        if (!$mysqli->real_connect($host, $user, $pass, null, $port)) {
            fwrite(STDERR, "FAIL: connect server\n");
            exit(1);
        }
    }
    $mysqli->set_charset('utf8');
    return $mysqli;
}

function splitSqlStatements(string $sql): array {
    $sql = preg_replace('~^\xEF\xBB\xBF~', '', $sql);
    $sql = preg_replace('~\r\n?~', "\n", $sql);

    $statements = [];
    $buffer = '';
    $inString = false;
    $stringChar = '';
    $len = strlen($sql);
    for ($i = 0; $i < $len; $i++) {
        $ch = $sql[$i];

        if (!$inString && $ch === '-' && ($i + 1 < $len) && $sql[$i + 1] === '-') {
            while ($i < $len && $sql[$i] !== "\n") $i++;
            $buffer .= "\n";
            continue;
        }
        if (!$inString && $ch === '#') {
            while ($i < $len && $sql[$i] !== "\n") $i++;
            $buffer .= "\n";
            continue;
        }
        if (!$inString && $ch === '/' && ($i + 1 < $len) && $sql[$i + 1] === '*') {
            $i += 2;
            while ($i + 1 < $len && !($sql[$i] === '*' && $sql[$i + 1] === '/')) $i++;
            $i++;
            continue;
        }

        if ($inString) {
            if ($ch === $stringChar) {
                $escaped = ($i > 0 && $sql[$i - 1] === '\\');
                if (!$escaped) {
                    $inString = false;
                    $stringChar = '';
                }
            }
            $buffer .= $ch;
            continue;
        }

        if ($ch === '\'' || $ch === '"') {
            $inString = true;
            $stringChar = $ch;
            $buffer .= $ch;
            continue;
        }

        if ($ch === ';') {
            $stmt = trim($buffer);
            if ($stmt !== '') $statements[] = $stmt;
            $buffer = '';
            continue;
        }

        $buffer .= $ch;
    }

    $stmt = trim($buffer);
    if ($stmt !== '') $statements[] = $stmt;
    return $statements;
}

function importSqlFile(mysqli $db, string $file, array $replacements): void {
    $sql = file_get_contents($file);
    if ($sql === false) {
        fwrite(STDERR, "FAIL: cannot read $file\n");
        exit(1);
    }

    $sql = str_replace(array_keys($replacements), array_values($replacements), $sql);
    $stmts = splitSqlStatements($sql);
    foreach ($stmts as $stmt) {
        if (!$db->query($stmt)) {
            fwrite(STDERR, "FAIL: SQL error: " . $db->error . "\n");
            exit(1);
        }
    }
}

if ($action === 'create_db') {
    $dbName = env('TRAVIANZ_SQL_DB');
    if ($dbName === null || $dbName === '') {
        fwrite(STDERR, "FAIL: TRAVIANZ_SQL_DB missing\n");
        exit(2);
    }
    if (!str_starts_with($dbName, 'travianz_e2e_')) {
        fwrite(STDERR, "FAIL: db name must start with travianz_e2e_\n");
        exit(2);
    }
    $mysqli = connect(false, true);
    if (!$mysqli->query("CREATE DATABASE IF NOT EXISTS `" . $mysqli->real_escape_string($dbName) . "` CHARACTER SET utf8 COLLATE utf8_general_ci")) {
        fwrite(STDERR, "FAIL: create db\n");
        exit(1);
    }
    echo "OK: create_db\n";
    exit(0);
}

if ($action === 'drop_db') {
    $dbName = env('TRAVIANZ_SQL_DB');
    if ($dbName === null || $dbName === '') {
        fwrite(STDERR, "FAIL: TRAVIANZ_SQL_DB missing\n");
        exit(2);
    }
    if (!str_starts_with($dbName, 'travianz_e2e_')) {
        fwrite(STDERR, "FAIL: refusing to drop non-e2e db\n");
        exit(2);
    }
    $mysqli = connect(false, true);
    $mysqli->query("DROP DATABASE IF EXISTS `" . $mysqli->real_escape_string($dbName) . "`");
    echo "OK: drop_db\n";
    exit(0);
}

if ($action === 'drop_prefix_tables') {
    $prefix = env('TRAVIANZ_TB_PREFIX', defined('TB_PREFIX') ? TB_PREFIX : '');
    if ($prefix === '') {
        fwrite(STDERR, "FAIL: missing prefix\n");
        exit(2);
    }
    $mysqli = connect(true);
    $p = $mysqli->real_escape_string($prefix) . '%';
    $res = $mysqli->query("SHOW TABLES LIKE '{$p}'");
    if (!$res) {
        fwrite(STDERR, "FAIL: SHOW TABLES\n");
        exit(1);
    }
    $tables = [];
    while ($row = $res->fetch_row()) {
        $tables[] = $row[0];
    }
    $mysqli->query("SET foreign_key_checks=0");
    foreach ($tables as $t) {
        $mysqli->query("DROP TABLE IF EXISTS `{$t}`");
    }
    echo "OK: drop_prefix_tables\n";
    exit(0);
}

if ($action === 'import_struct') {
    $prefix = env('TRAVIANZ_TB_PREFIX');
    if ($prefix === null || $prefix === '') {
        fwrite(STDERR, "FAIL: TRAVIANZ_TB_PREFIX missing\n");
        exit(2);
    }
    $mysqli = connect(true);
    importSqlFile($mysqli, $root . '/var/db/struct.sql', [
        '%PREFIX%' => $prefix,
        '%prefix%' => $prefix,
    ]);
    echo "OK: import_struct\n";
    exit(0);
}

if ($action === 'build_worlddata') {
    require_once $root . '/GameEngine/Database.php';
    global $database;
    if (!isset($database)) {
        fwrite(STDERR, "FAIL: database not initialized\n");
        exit(1);
    }

    $ret = $database->populateWorldData();
    if ($ret === false || $ret === -1) {
        fwrite(STDERR, "FAIL: populateWorldData\n");
        exit(1);
    }

    $total = $database->TotalCroppers();
    $res = $database->populateCroppers($total, true, 20000, null);
    if (!is_array($res) || !($res['ok'] ?? false)) {
        fwrite(STDERR, "FAIL: populateCroppers\n");
        exit(1);
    }

    echo "OK: build_worlddata\n";
    exit(0);
}

if ($action === 'count_worlddata') {
    $prefix = env('TRAVIANZ_TB_PREFIX', defined('TB_PREFIX') ? TB_PREFIX : '');
    if ($prefix === '') {
        fwrite(STDERR, "FAIL: missing prefix\n");
        exit(2);
    }
    $mysqli = connect(true);
    $tables = [
        'wdata' => 1,
        'odata' => 1,
        'units' => 1,
        'croppers' => 1,
    ];
    $out = [];
    foreach ($tables as $t => $_) {
        $res = $mysqli->query("SELECT COUNT(*) AS c FROM `{$prefix}{$t}`");
        if (!$res) {
            fwrite(STDERR, "FAIL: query {$prefix}{$t}\n");
            exit(1);
        }
        $row = $res->fetch_assoc();
        $out[$t] = (int) ($row['c'] ?? 0);
    }
    echo json_encode($out, JSON_UNESCAPED_SLASHES) . "\n";
    exit(0);
}

if ($action === 'get_user_id') {
    $username = $argv[2] ?? '';
    if ($username === '') {
        fwrite(STDERR, "FAIL: missing username\n");
        exit(2);
    }
    $mysqli = connect(true);
    $prefix = env('TRAVIANZ_TB_PREFIX', defined('TB_PREFIX') ? TB_PREFIX : '');
    $u = $mysqli->real_escape_string($username);
    $res = $mysqli->query("SELECT id FROM `{$prefix}users` WHERE username='{$u}' LIMIT 1");
    $row = $res ? $res->fetch_assoc() : null;
    $id = (int) ($row['id'] ?? 0);
    echo $id . "\n";
    exit(0);
}

if ($action === 'has_message') {
    $ownerId = (int) ($argv[2] ?? 0);
    $topic = $argv[3] ?? '';
    if ($ownerId <= 0 || $topic === '') {
        fwrite(STDERR, "FAIL: missing ownerId/topic\n");
        exit(2);
    }
    $mysqli = connect(true);
    $prefix = env('TRAVIANZ_TB_PREFIX', defined('TB_PREFIX') ? TB_PREFIX : '');
    $topicEsc = $mysqli->real_escape_string($topic);
    $res = $mysqli->query("SELECT id FROM `{$prefix}mdata` WHERE owner={$ownerId} AND topic='{$topicEsc}' ORDER BY id DESC LIMIT 1");
    $row = $res ? $res->fetch_assoc() : null;
    echo ((int) ($row['id'] ?? 0)) . "\n";
    exit(0);
}

if ($action === 'detect_prefix') {
    $mysqli = connect(true);
    $res = $mysqli->query("SHOW TABLES LIKE '%users'");
    if (!$res) {
        fwrite(STDERR, "FAIL: SHOW TABLES\n");
        exit(1);
    }
    $tables = [];
    while ($row = $res->fetch_row()) {
        $tables[] = $row[0];
    }
    echo json_encode($tables, JSON_UNESCAPED_SLASHES) . "\n";
    exit(0);
}

if ($action === 'get_main_village_id') {
    $uid = (int) ($argv[2] ?? 0);
    if ($uid <= 0) {
        fwrite(STDERR, "FAIL: missing uid\n");
        exit(2);
    }
    $mysqli = connect(true);
    $prefix = env('TRAVIANZ_TB_PREFIX', defined('TB_PREFIX') ? TB_PREFIX : '');
    $res = $mysqli->query("SELECT wref FROM `{$prefix}vdata` WHERE owner={$uid} AND capital=1 ORDER BY wref LIMIT 1");
    $row = $res ? $res->fetch_assoc() : null;
    $wref = (int) ($row['wref'] ?? 0);
    if ($wref <= 0) {
        $res2 = $mysqli->query("SELECT wref FROM `{$prefix}vdata` WHERE owner={$uid} ORDER BY capital DESC, wref ASC LIMIT 1");
        $row2 = $res2 ? $res2->fetch_assoc() : null;
        $wref = (int) ($row2['wref'] ?? 0);
    }
    echo $wref . "\n";
    exit(0);
}

if ($action === 'set_building_level') {
    $vref = (int) ($argv[2] ?? 0);
    $slot = (int) ($argv[3] ?? 0);
    $gid  = (int) ($argv[4] ?? 0);
    $level= (int) ($argv[5] ?? 0);
    if ($vref <= 0 || $slot <= 0 || $slot > 40 || $gid <= 0 || $level <= 0) {
        fwrite(STDERR, "FAIL: missing vref/slot/gid/level\n");
        exit(2);
    }
    $mysqli = connect(true);
    $prefix = env('TRAVIANZ_TB_PREFIX', defined('TB_PREFIX') ? TB_PREFIX : '');
    $res = $mysqli->query("SELECT * FROM `{$prefix}fdata` WHERE vref={$vref} LIMIT 1");
    if (!$res) {
        fwrite(STDERR, "FAIL: select fdata\n");
        exit(1);
    }
    $row = $res->fetch_assoc();
    if (!$row) {
        fwrite(STDERR, "FAIL: fdata missing\n");
        exit(1);
    }
    $tcol = "f{$slot}t";
    $lcol = "f{$slot}";
    $level = max(0, min(20, $level));
    $q = "UPDATE `{$prefix}fdata` SET `{$tcol}`={$gid}, `{$lcol}`={$level} WHERE vref={$vref}";
    if (!$mysqli->query($q)) {
        fwrite(STDERR, "FAIL: update building level\n");
        exit(1);
    }
    echo "OK: slot={$slot} gid={$gid} level={$level}\n";
    exit(0);
}

if ($action === 'set_user_gold') {
    $uid = (int) ($argv[2] ?? 0);
    $gold = (int) ($argv[3] ?? 0);
    if ($uid <= 0) {
        fwrite(STDERR, "FAIL: missing uid\n");
        exit(2);
    }
    $mysqli = connect(true);
    $prefix = env('TRAVIANZ_TB_PREFIX', defined('TB_PREFIX') ? TB_PREFIX : '');
    $q = "UPDATE `{$prefix}users` SET gold={$gold} WHERE id={$uid}";
    if (!$mysqli->query($q)) {
        fwrite(STDERR, "FAIL: update gold\n");
        exit(1);
    }
    echo "OK: gold={$gold}\n";
    exit(0);
}

if ($action === 'get_user_gold') {
    $uid = (int) ($argv[2] ?? 0);
    if ($uid <= 0) {
        fwrite(STDERR, "FAIL: missing uid\n");
        exit(2);
    }
    $mysqli = connect(true);
    $prefix = env('TRAVIANZ_TB_PREFIX', defined('TB_PREFIX') ? TB_PREFIX : '');
    $res = $mysqli->query("SELECT gold FROM `{$prefix}users` WHERE id={$uid} LIMIT 1");
    $row = $res ? $res->fetch_assoc() : null;
    echo (int) ($row['gold'] ?? 0) . "\n";
    exit(0);
}

if ($action === 'get_last_market_offer') {
    $vref = (int) ($argv[2] ?? 0);
    if ($vref <= 0) {
        fwrite(STDERR, "FAIL: missing vref\n");
        exit(2);
    }
    $mysqli = connect(true);
    $prefix = env('TRAVIANZ_TB_PREFIX', defined('TB_PREFIX') ? TB_PREFIX : '');
    $res = $mysqli->query("SELECT id FROM `{$prefix}market` WHERE vref={$vref} AND accept=0 ORDER BY id DESC LIMIT 1");
    $row = $res ? $res->fetch_assoc() : null;
    echo (int) ($row['id'] ?? 0) . "\n";
    exit(0);
}

if ($action === 'set_embassy_level') {
    $vref = (int) ($argv[2] ?? 0);
    $level = (int) ($argv[3] ?? 0);
    if ($vref <= 0 || $level <= 0) {
        fwrite(STDERR, "FAIL: missing vref/level\n");
        exit(2);
    }
    $mysqli = connect(true);
    $prefix = env('TRAVIANZ_TB_PREFIX', defined('TB_PREFIX') ? TB_PREFIX : '');
    $res = $mysqli->query("SELECT * FROM `{$prefix}fdata` WHERE vref={$vref} LIMIT 1");
    if (!$res) {
        fwrite(STDERR, "FAIL: select fdata\n");
        exit(1);
    }
    $row = $res->fetch_assoc();
    if (!$row) {
        fwrite(STDERR, "FAIL: fdata missing\n");
        exit(1);
    }
    $slot = 0;
    for ($i = 1; $i <= 40; $i++) {
        $tcol = "f{$i}t";
        if ((int) ($row[$tcol] ?? 0) === 18) {
            $slot = $i;
            break;
        }
    }
    if ($slot === 0) {
        for ($i = 19; $i <= 40; $i++) {
            $tcol = "f{$i}t";
            if ((int) ($row[$tcol] ?? 0) === 0) {
                $slot = $i;
                break;
            }
        }
    }
    if ($slot === 0) {
        $slot = 19;
    }
    $tcol = "f{$slot}t";
    $lcol = "f{$slot}";
    $level = max(0, min(20, $level));
    $q = "UPDATE `{$prefix}fdata` SET `{$tcol}`=18, `{$lcol}`={$level} WHERE vref={$vref}";
    if (!$mysqli->query($q)) {
        fwrite(STDERR, "FAIL: update embassy level\n");
        exit(1);
    }
    echo "OK: embassy slot={$slot} level={$level}\n";
    exit(0);
}

if ($action === 'get_alliance_id_by_tag') {
    $tag = $argv[2] ?? '';
    if ($tag === '') {
        fwrite(STDERR, "FAIL: missing tag\n");
        exit(2);
    }
    $mysqli = connect(true);
    $prefix = env('TRAVIANZ_TB_PREFIX', defined('TB_PREFIX') ? TB_PREFIX : '');
    $tagEsc = $mysqli->real_escape_string($tag);
    $res = $mysqli->query("SELECT id FROM `{$prefix}alidata` WHERE tag='{$tagEsc}' LIMIT 1");
    $row = $res ? $res->fetch_assoc() : null;
    echo ((int) ($row['id'] ?? 0)) . "\n";
    exit(0);
}

if ($action === 'count_ali_log_for_aid') {
    $aid = (int) ($argv[2] ?? 0);
    if ($aid <= 0) {
        fwrite(STDERR, "FAIL: missing aid\n");
        exit(2);
    }
    $mysqli = connect(true);
    $prefix = env('TRAVIANZ_TB_PREFIX', defined('TB_PREFIX') ? TB_PREFIX : '');
    $res = $mysqli->query("SELECT COUNT(*) AS c FROM `{$prefix}ali_log` WHERE aid={$aid}");
    $row = $res ? $res->fetch_assoc() : null;
    echo (int) ($row['c'] ?? 0) . "\n";
    exit(0);
}

if ($action === 'get_invitation_id_for_user') {
    $uid = (int) ($argv[2] ?? 0);
    if ($uid <= 0) {
        fwrite(STDERR, "FAIL: missing uid\n");
        exit(2);
    }
    $mysqli = connect(true);
    $prefix = env('TRAVIANZ_TB_PREFIX', defined('TB_PREFIX') ? TB_PREFIX : '');
    $res = $mysqli->query("SELECT id FROM `{$prefix}ali_invite` WHERE uid={$uid} ORDER BY id DESC LIMIT 1");
    $row = $res ? $res->fetch_assoc() : null;
    echo (int) ($row['id'] ?? 0) . "\n";
    exit(0);
}

if ($action === 'get_user_alliance') {
    $uid = (int) ($argv[2] ?? 0);
    if ($uid <= 0) {
        fwrite(STDERR, "FAIL: missing uid\n");
        exit(2);
    }
    $mysqli = connect(true);
    $prefix = env('TRAVIANZ_TB_PREFIX', defined('TB_PREFIX') ? TB_PREFIX : '');
    $res = $mysqli->query("SELECT alliance FROM `{$prefix}users` WHERE id={$uid} LIMIT 1");
    $row = $res ? $res->fetch_assoc() : null;
    echo (int) ($row['alliance'] ?? 0) . "\n";
    exit(0);
}

if ($action === 'get_alliance_json') {
    $aid = (int) ($argv[2] ?? 0);
    if ($aid <= 0) {
        fwrite(STDERR, "FAIL: missing aid\n");
        exit(2);
    }
    $mysqli = connect(true);
    $prefix = env('TRAVIANZ_TB_PREFIX', defined('TB_PREFIX') ? TB_PREFIX : '');
    $res = $mysqli->query("SELECT id,name,tag,max,notice,`desc`,forumlink FROM `{$prefix}alidata` WHERE id={$aid} LIMIT 1");
    $row = $res ? $res->fetch_assoc() : null;
    echo json_encode($row ?? [], JSON_UNESCAPED_SLASHES) . "\n";
    exit(0);
}

if ($action === 'get_alli_permissions_json') {
    $uid = (int) ($argv[2] ?? 0);
    $aid = (int) ($argv[3] ?? 0);
    if ($uid <= 0 || $aid <= 0) {
        fwrite(STDERR, "FAIL: missing uid/aid\n");
        exit(2);
    }
    $mysqli = connect(true);
    $prefix = env('TRAVIANZ_TB_PREFIX', defined('TB_PREFIX') ? TB_PREFIX : '');
    $res = $mysqli->query("SELECT * FROM `{$prefix}ali_permission` WHERE uid={$uid} AND alliance={$aid} LIMIT 1");
    $row = $res ? $res->fetch_assoc() : null;
    echo json_encode($row ?? [], JSON_UNESCAPED_SLASHES) . "\n";
    exit(0);
}

fwrite(STDERR, "FAIL: unknown action\n");
exit(2);
