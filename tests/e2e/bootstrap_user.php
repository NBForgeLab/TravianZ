<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);

$_SERVER['PHP_SELF'] ??= '/tests/e2e/bootstrap_user.php';
$_SERVER['REMOTE_ADDR'] ??= '127.0.0.1';

require $root . '/GameEngine/Database.php';

global $database;
if (!isset($database)) {
    fwrite(STDERR, "FAIL: database not initialized\n");
    exit(1);
}

$username = getenv('E2E_USERNAME') ?: 'e2e_user';
$password = getenv('E2E_PASSWORD') ?: 'e2e_pass_12345';
$email    = getenv('E2E_EMAIL') ?: 'e2e@travianz.local';
$tribe    = (int) (getenv('E2E_TRIBE') ?: 1);

$usernameEsc = $database->escape($username);
$res = mysqli_query($database->dblink, "SELECT id FROM " . TB_PREFIX . "users WHERE username = '{$usernameEsc}' LIMIT 1");
if (!$res) {
    fwrite(STDERR, "FAIL: could not query users\n");
    exit(1);
}
$row = mysqli_fetch_assoc($res);
$uid = (int) ($row['id'] ?? 0);

if ($uid <= 0) {
    $hash = trz_password_hash($password);
    $uid = (int) $database->register($username, $hash, $email, $tribe, '');
    if ($uid <= 0) {
        fwrite(STDERR, "FAIL: could not create user\n");
        exit(1);
    }
}

$vRes = mysqli_query($database->dblink, "SELECT wref FROM " . TB_PREFIX . "vdata WHERE owner = {$uid} LIMIT 1");
if (!$vRes) {
    fwrite(STDERR, "FAIL: could not query vdata\n");
    exit(1);
}
$vRow = mysqli_fetch_assoc($vRes);
$wid = (int) ($vRow['wref'] ?? 0);

if ($wid <= 0) {
    $kid = 1;
    $database->generateVillages(
        [['wid' => 0, 'mode' => 0, 'type' => 3, 'kid' => $kid, 'capital' => 1, 'pop' => 2, 'name' => null, 'natar' => 0]],
        $uid,
        $username
    );
}

echo "OK: user={$username} uid={$uid}\n";
exit(0);
