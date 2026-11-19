<?php
#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Filename       editHero.php                                                ##
##  Type           BACKEND                                                     ##
##  Developed by:  Dzoki & Advocatie                                           ##
##  License:       TravianZ Project                                            ##
##  Reworks by:    ronix                                                       ##
##  Copyright:     TravianZ (c) 2010-2014. All rights reserved.                ##
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
include_once($autoprefix . "GameEngine/Data/hero_full.php");

$status = "&ce=1";

if (isset($_POST['id'], $_POST['hid'])) {
    $id   = (int)$_POST['id'];
    $hid  = (int)$_POST['hid'];
    $hname = trim($_POST['hname'] ?? '');

    if ($hname === '') {
        header("Location: ../../../Admin/admin.php?p=editHero&uid=$id&e=1");
        exit;
    }

    // Input curat - cast individual, NU escape global
    $hunit   = (int)($_POST['hunit'] ?? 0);
    // plafonul vine din tabelul de niveluri (ultimul index e santinela), ca sa
    // urmeze automat extinderea facuta pentru al 6-lea atribut
    $hMaxLevel = isset($hero_levels) ? max(array_keys($hero_levels)) - 1 : 119;
    $hlvl    = max(0, min($hMaxLevel, (int)($_POST['hlvl'] ?? 0)));
    $exp     = (int)($_POST['exp'] ?? 0);
    $hhealth = (float)($_POST['hhealth'] ?? 100);
    $hatk    = (int)($_POST['hatk'] ?? 0);
    $hdef    = (int)($_POST['hdef'] ?? 0);
    $hob     = (int)($_POST['hob'] ?? 0);
    $hdb     = (int)($_POST['hdb'] ?? 0);
    $hrege   = (int)($_POST['hrege'] ?? 0);
    $hres    = (int)($_POST['hres'] ?? 0);
    $hrestype = (int)($_POST['hrestype'] ?? 0);

    // atributele nu pot depasi 100 fiecare (la fel ca in joc)
    foreach (['hatk','hdef','hob','hdb','hrege','hres'] as $hAttr) {
        if ($$hAttr < 0)   { $$hAttr = 0; }
        if ($$hAttr > 100) { $$hAttr = 100; }
    }

    if ($hrestype < 0 || $hrestype > 4) { $hrestype = 0; }

    $experience = isset($hero_levels[$hlvl]) ? (int)$hero_levels[$hlvl] : 0;

    $heroService = new \App\Admin\Services\HeroSettingsService($database, TB_PREFIX);
    $return = $heroService->updateHero($id, $hid, [
        'unit'         => $hunit,
        'name'         => $hname,
        'level'        => $hlvl,
        'points'       => $exp,
        'experience'   => $experience,
        'health'       => $hhealth,
        'attack'       => $hatk,
        'defence'      => $hdef,
        'attackbonus'  => $hob,
        'defencebonus' => $hdb,
        'regeneration' => $hrege,
        'resources'    => $hres,
        'res_type'     => $hrestype,
    ]);

    if ($return) {
        $adminId = (int)$_SESSION['id'];
        $heroService->logAdminAction($adminId, "Changed hero info for user <a href='admin.php?p=player&uid=$id'>$id</a> (hero $hid)");
        $status = "&cs=1";
    }
}

header("Location: ../../../Admin/admin.php?p=player&uid=" . (int)$id . $status);
exit;
?>