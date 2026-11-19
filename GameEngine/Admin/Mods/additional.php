<?php

#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Filename       additional.php                                              ##
##  Developed by:  aggenkeech                                                  ##
##  License:       TravianZ Project                                            ##
##  Copyright:     TravianZ (c) 2010-2025. All rights reserved.                ##
##                                                                             ##
#################################################################################

require_once(__DIR__ . '/../../../autoloader.php');
include_once("../../config.php");
include_once("../../Database.php");

use App\Admin\Services\AdditionalService;

// #299: load CSRF helpers + admin_deny() before the access check below.
require_once(__DIR__ . '/../csrf.php');
if (!isset($_SESSION)) session_start();
if(($_SESSION['access']?? 0) < ADMIN) admin_deny('You must be signed in as an administrator to view this page. Your session may have expired — please return to the admin panel and sign in again.');

// Issue #139: this Mod is POSTed to directly, so it must verify the CSRF token
// itself (it does not go through admin.php's central csrf_verify()).
require_once(__DIR__ . '/../csrf.php');
csrf_verify();

// --- INPUT ---
$id = (int)($_POST['id']?? 0);
$admid = (int)($_POST['admid']?? 0);
$access = (int)($_POST['access']?? 2);
$newGold = (int)($_POST['gold']?? 0);
$sit1 = (int)($_POST['sitter1']?? 0);
$sit2 = (int)($_POST['sitter2']?? 0);
$protect = time() + ((int)($_POST['protect']?? 0) * 86400);
$cp = (int)($_POST['cp']?? 0);
$ap = (int)($_POST['off']?? 0);
$dp = (int)($_POST['def']?? 0);
$rr = (int)($_POST['res']?? 0);
$apall = (int)($_POST['ooff']?? 0);
$dpall = (int)($_POST['odef']?? 0);
$vac_mode = (int)($_POST['vac_mode']?? 0);

if($id <= 0) die("Invalid user");

// --- ADMIN SERVICE ---
$adminService = new AdditionalService($database);
$adminUid = (int)($_SESSION['id'] ?? 0) > 0 ? (int)$_SESSION['id'] : $admid;
$adminName = $database->getUserField($adminUid, 'username', 0)?: 'Admin';
$playerName = $database->getUserField($id, 'username', 0)?: 'Unknown';

$adminService->updateAdditional(
    $id, $access, $newGold, $sit1, $sit2, $protect, $cp, $ap, $dp, $rr, $apall, $dpall, $vac_mode,
    $adminUid, $adminName, $playerName
);

// --- REDIRECT ---
header("Location:../../../Admin/admin.php?p=player&uid=".$id);
exit;
