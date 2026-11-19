<?php
#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Filename       delallymedal.php                                            ##
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

include_once($autoprefix . "GameEngine/config.php");
include_once($autoprefix . "GameEngine/Database.php");

use App\Admin\Services\AllianceAdminService;
$allianceService = new AllianceAdminService($database, TB_PREFIX);

// ---------------------------------------------------------------------------
// Input
// ---------------------------------------------------------------------------
$delete  = (int)($_POST['medalid'] ?? 0); // id din allimedal
$aid     = (int)($_POST['aid'] ?? 0);     // id alianță
$session = (int)($_POST['admid'] ?? 0);

if ($delete <= 0 || $aid <= 0) {
    header("Location: ../../../Admin/admin.php?p=alliance&aid=$aid&e=bad");
    exit;
}

// ---------------------------------------------------------------------------
// Verificare admin - păstrăm logica originală
// ---------------------------------------------------------------------------
$admin = $database->getUserArray($session, 1);
if (!$admin || (int)$admin['access'] !== 9) {
    admin_deny('You must be signed in as an administrator to view this page. Your session may have expired — please return to the admin panel and sign in again.');
}

// ---------------------------------------------------------------------------
// Ștergere logică medalie alianță
// ---------------------------------------------------------------------------
$allianceService->deleteAllianceMedal($delete, $aid);

// ---------------------------------------------------------------------------
// Log admin
// ---------------------------------------------------------------------------
$adminId = (int)$_SESSION['id'];
$allianceService->logAdminAction($adminId, "Deleted ally medal #$delete for ally $aid");

header("Location: ../../../Admin/admin.php?p=alliance&aid=" . $aid);
exit;
?>