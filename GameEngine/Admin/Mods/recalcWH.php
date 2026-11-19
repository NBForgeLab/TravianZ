<?php
#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Filename       recalcWH.php (salvează clădirile (f1-f40)           		   ##
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
$villageService = new \App\Admin\Services\VillageService($database);

// ---------------------------------------------------------------------------
// Verificare admin
// ---------------------------------------------------------------------------
$session = (int)($_POST['admid'] ?? 0);
$id = (int)($_POST['id'] ?? 0);

$admin = $database->getUserArray($session, 1);
if (!$admin || (int)$admin['access'] !== 9) {
    admin_deny('You must be signed in as an administrator to view this page. Your session may have expired — please return to the admin panel and sign in again.');
}

if ($id <= 0) {
    header("Location: ../../../Admin/admin.php?p=villages");
    exit;
}

// ---------------------------------------------------------------------------
// Construiește SET dinamic f1-f40
// ---------------------------------------------------------------------------
$buildings = [];
for ($i = 1; $i <= 40; $i++) {
    $buildings["id{$i}level"] = (int)($_POST["id{$i}level"] ?? 0);
    $buildings["id{$i}gid"] = (int)($_POST["id{$i}gid"] ?? 0);
}

// ---------------------------------------------------------------------------
// Update
// ---------------------------------------------------------------------------
$adminId = (int)$_SESSION['id'];
$villageService->updateVillageBuildings($id, $buildings, $adminId);

header("Location: ../../../Admin/admin.php?p=village&did=" . $id);
exit;
?>