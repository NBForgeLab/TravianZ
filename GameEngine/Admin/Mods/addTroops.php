<?php

#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Filename       addTroops.php                                               ##
##  Type           BACKEND                                                     ##
##  Developed by:  Dzoki & Advocatie                                           ##
##  License:       TravianZ Project                                            ##
##  Reworks by:    ronix                                                       ##
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
require_once(__DIR__ . '/../../../autoloader.php');

include_once __DIR__ . "/../../Database.php";
include_once __DIR__ . "/../../Technology.php";
include_once __DIR__ . "/../../Data/unitdata.php";
use App\Admin\Services\AddTroopsHandler;

/* ---------------------------------------------------------------------------
 * Input & validare
 * --------------------------------------------------------------------------- */
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    header("Location: ../../../Admin/admin.php");
    exit;
}

$handler = new AddTroopsHandler($database);
$handler->handle($_POST, $_SESSION['id']);

header("Location: ../../../Admin/admin.php?p=village&did=" . $id . "&d");
exit;
?>