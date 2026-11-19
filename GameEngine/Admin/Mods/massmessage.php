<?php

#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Filename       massmessage.php                                             ##
##  Type           BACKEND                                                     ##
##  Developed by:  Shadow                                                      ##
##  License:       TravianZ Project                                            ##
##  Copyright:     TravianZ (c) 2010-2025. All rights reserved.                ##
##                                                                             ##
#################################################################################

// #299: load CSRF helpers + admin_deny() before the access check below.
require_once(__DIR__ . '/../csrf.php');

// csrf.php porneste deja sesiunea; un al doilea apel doar genereaza un Notice.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once("../../config.php");
include_once("../../Database.php");

if (!isset($_SESSION['access']) || $_SESSION['access'] < ADMIN) {
    admin_deny('You must be signed in as an administrator to view this page. Your session may have expired — please return to the admin panel and sign in again.');
}

// Issue #139: this Mod is POSTed to directly, so it must verify the CSRF token
// itself (it does not go through admin.php's central csrf_verify()).
require_once(__DIR__ . '/../csrf.php');
csrf_verify();
require_once(__DIR__ . '/../../../autoloader.php');

/*
|--------------------------------------------------------------------------
| PREPARE
|--------------------------------------------------------------------------
*/
if (
    isset($_POST['action']) &&
    $_POST['action'] == 'prepare'
) {

    if (empty($_POST['subject'])) {
        die("Subject required");
    }

    if (empty($_POST['message'])) {
        die("Message required");
    }

    $_SESSION['mass_subject'] = trim($_POST['subject']);
    $_SESSION['mass_message'] = trim($_POST['message']);
	$allowedColors = array(
    'black',
    'red',
    'green',
    'blue',
    'orange',
    'purple',
    'brown'
	);

	$color = strtolower(trim($_POST['color']));

	if (!in_array($color, $allowedColors, true)) {
    $color = 'black';
	}

	$_SESSION['mass_color'] = $color;

    header("Location: ../../../Admin/admin.php?p=massmessage&confirm=1");
    exit;
}

/*
|--------------------------------------------------------------------------
| EXECUTE
|--------------------------------------------------------------------------
*/
if (
    isset($_POST['action']) &&
    $_POST['action'] == 'execute'
) {

    if (
        empty($_SESSION['mass_subject']) ||
        empty($_SESSION['mass_message'])
    ) {
        header("Location: ../../../Admin/admin.php?p=massmessage");
        exit;
    }

    $subject = $_SESSION['mass_subject'];
    $message = $_SESSION['mass_message'];
    $color = $_SESSION['mass_color'] ?: 'black';

    $service = new \App\Admin\Services\MassMessageService($database);
    $service->sendMassMessage($subject, $message, $color);

    unset(
        $_SESSION['mass_subject'],
        $_SESSION['mass_message'],
        $_SESSION['mass_color']
    );

    header("Location: ../../../Admin/admin.php?p=massmessage&done=1");
    exit;
}

header("Location: ../../../Admin/admin.php?p=massmessage");
exit;
?>