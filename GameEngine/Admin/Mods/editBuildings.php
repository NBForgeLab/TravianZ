<?php
#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Filename       editBuildings.php                                           ##
##  Type        BACKEND                                                        ##
##  Developed by:  aggenkeech                                                  ##
##  Fix by:        ronix + Shadow 2026 (WW lvl 100 + auto pop)                ##
##  License:       TravianZ Project                                            ##
##  Copyright:     TravianZ (c) 2011-2026. All rights reserved.                ##
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
if (!defined('AUTOMATION_MANUAL_RUN')) {
    define('AUTOMATION_MANUAL_RUN', true);
}
include_once($autoprefix . "GameEngine/Automation.php");
use App\Admin\Services\EditBuildingsHandler;

// ---------------------------------------------------------------------------
// Input
// ---------------------------------------------------------------------------
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    header("Location: ../../../Admin/admin.php?p=admin");
    exit;
}

if (!isset($automation) || !($automation instanceof Automation)) {
    $automation = new Automation($database);
}
$handler = new EditBuildingsHandler($database, $automation);
$handler->handle($_POST, (int)$_SESSION['id']);

header("Location: ../../../Admin/admin.php?p=village&did=" . $id);
exit;
?>