<?php
#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Filename       delUser.php                                                 ##
##  Type           BACKEND                                                     ##
##  Developed by:  Shadow (după model delAli)                                  ##
##  License:       TravianZ Project                                            ##
##  Copyright:     TravianZ (c) 2010-2025. All rights reserved.                 ##
#################################################################################

// #299: load CSRF helpers + admin_deny() before the access check below.
require_once(__DIR__ . '/../csrf.php');
if (!isset($_SESSION)) { session_start(); }
if (empty($_SESSION['access']) || $_SESSION['access'] < 9) {
    admin_deny('You must be signed in as an administrator to view this page. Your session may have expired — please return to the admin panel and sign in again.');
}

// Issue #139: this Mod is POSTed to directly, so it must verify the CSRF token
// itself (it does not go through admin.php's central csrf_verify()).
require_once(__DIR__ . '/../csrf.php');
csrf_verify();

include_once("../../config.php");

// ---------------------------------------------------------------------------
// Autoloader - identic cu delAli.php
// ---------------------------------------------------------------------------
$autoprefix = '';
for ($i = 0; $i < 5; $i++) {
    $autoprefix = str_repeat('../', $i);
    if (file_exists($autoprefix . 'autoloader.php')) { break; }
}
include_once($autoprefix . "GameEngine/Database.php");
use App\Admin\Services\DeleteUserHandler;

// ---------------------------------------------------------------------------
// Input
// ---------------------------------------------------------------------------
$uid   = (int)($_POST['uid'] ?? 0);
$admid = (int)($_POST['admid'] ?? 0);
$pass  = $_POST['pass'] ?? '';

if ($uid <= 0 || $admid <= 0) {
    header("Location: ../../../Admin/admin.php?p=search&e=bad");
    exit;
}

// ---------------------------------------------------------------------------
// Verificare admin + parolă
// ---------------------------------------------------------------------------
$admin = $database->getUserArray($admid, 1);
if (!$admin || (int)$admin['access'] !== 9) {
    admin_deny('You must be signed in as an administrator to view this page. Your session may have expired — please return to the admin panel and sign in again.');
}
if (!password_verify($pass, $admin['password'])) {
    die('<script>alert("Parola admin gresita!");history.back();</script>');
}
if ($uid == $admid) {
    die('<script>alert("Nu te poti sterge pe tine!");history.back();</script>');
}

$handler = new DeleteUserHandler($database);
$handler->handle($uid, $admid);

header("Location: ../../../Admin/admin.php?p=search&deluser=1");
exit;
