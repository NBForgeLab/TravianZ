<?php
#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Filename       editUsername.php                                            ##
##  Type           BACKEND                                                     ##
##  Developed by:  aggenkeech                                                  ##
##  License:       TravianZ Project                                            ##
##  Copyright:     TravianZ (c) 2010-2025. All rights reserved.                ##
##                                                                             ##
#################################################################################

// #299: load CSRF helpers + admin_deny() before the access check below.
require_once(__DIR__ . '/../csrf.php');
if (!isset($_SESSION)) {
    session_start();
}
if (empty($_SESSION['access']) || $_SESSION['access'] < 9) {
    admin_deny('You must be signed in as an administrator to view this page. Your session may have expired — please return to the admin panel and sign in again.');
}

// Issue #139: this Mod is POSTed to directly, so it must verify the CSRF token
// itself (it does not go through admin.php's central csrf_verify()).
require_once(__DIR__ . '/../csrf.php');
csrf_verify();

include_once("../../config.php");

// ---------------------------------------------------------------------------
// Autoloader path
// ---------------------------------------------------------------------------
$autoprefix = '';
for ($i = 0; $i < 5; $i++) {
    $autoprefix = str_repeat('../', $i);
    if (file_exists($autoprefix . 'autoloader.php')) {
        break;
    }
}

include_once($autoprefix . "GameEngine/Database.php");

// ---------------------------------------------------------------------------
// Input
// ---------------------------------------------------------------------------
$uid     = (int)($_POST['uid'] ?? 0);
$session = (int)($_POST['admid'] ?? 0);
$username = trim($_POST['username'] ?? '');

if ($uid <= 0 || $session <= 0 || $username === '') {
    header("Location: ../../../Admin/admin.php?p=player&uid=$uid&e=user");
    exit;
}

// ---------------------------------------------------------------------------
// Verificare admin
// ---------------------------------------------------------------------------
$admin = $database->getUserArray($session, 1);
if (!$admin || (int)$admin['access'] !== 9) {
    admin_deny('You must be signed in as an administrator to view this page. Your session may have expired — please return to the admin panel and sign in again.');
}

$handler = new \App\Admin\Services\EditUsernameHandler($database);
$result = $handler->handle($uid, $username, (int)$_SESSION['id']);

if ($result['status'] === 'invalid') {
    header("Location: ../../../Admin/admin.php?p=player&uid=$uid&e=invalid");
    exit;
} elseif ($result['status'] === 'taken') {
    header("Location: ../../../Admin/admin.php?p=player&uid=$uid&e=taken");
    exit;
}

header("Location: ../../../Admin/admin.php?p=player&uid=" . $uid . "&name=1");
exit;
?>