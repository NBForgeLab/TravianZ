<?php
#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Filename       sendMessage.php                                             ##
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
$messageService = new \App\Admin\Services\MessageService($database);

// ---------------------------------------------------------------------------
// INPUT
// ---------------------------------------------------------------------------
$adminId = (int)($_SESSION['id'] ?? 0);
$uid     = (int)($_POST['uid'] ?? 0);
$topic   = trim($_POST['topic'] ?? 'Admin Message');
$message = trim($_POST['message'] ?? '');

// ---------------------------------------------------------------------------
// VALIDARE
// ---------------------------------------------------------------------------
if ($adminId <= 0) {
    die("Invalid admin session.");
}

if ($uid <= 0 || $message === '') {
    header("Location: ../../../Admin/admin.php?p=Newmessage&uid=$uid&e=1");
    exit;
}

// ---------------------------------------------------------------------------
// SEND MESSAGE
// ---------------------------------------------------------------------------
$messageService->sendMessage($uid, $adminId, $topic, $message);

// ---------------------------------------------------------------------------
// REDIRECT SUCCESS
// ---------------------------------------------------------------------------
header("Location: ../../../Admin/admin.php?p=Newmessage&uid=" . $uid . "&msg=ok");
exit;
?>