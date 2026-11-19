<?php
#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Filename       editCronSet.php                                             ##
##  Type           Admin Panel - Cron & Automation settings                    ##
## --------------------------------------------------------------------------- ##
##  Contact        cata7007@gmail.com                                          ##
##  Project        TravianZ                                                    ##
##  GitHub         https://github.com/Shadowss/TravianZ                        ##
##  License        TravianZ Project                                            ##
##  Copyright      TravianZ (c) 2010-2026. All rights reserved.                ##
## --------------------------------------------------------------------------- ##
##  NOTA DE PROIECTARE - de ce acest mod NU regenereaza tot config.php:        ##
##    Celelalte module edit*.php reconstruiesc config.php din constant_format  ##
##    .tpl si trebuie sa reumple TOATE placeholderele (peste 100). Aici avem   ##
##    de schimbat trei valori, iar o gresala intr-o mapare fara legatura ar    ##
##    strica setari de joc. De aceea modificam DOAR liniile de cron din        ##
##    config.php existent; restul fisierului ramane bit-cu-bit neatins.        ##
##    Daca liniile lipsesc (server instalat inainte de aceasta versiune), ele  ##
##    sunt inserate dupa AUTOMATION_LOCK_FILE_NAME.                            ##
#################################################################################

if (!isset($_SESSION)) {
    session_start();
}

if ($_SESSION['access'] < 9) {
    die(ACCESS_DENIED_ADMIN);
}

// Acest mod primeste POST direct, deci isi verifica singur tokenul CSRF
// (nu trece prin csrf_verify()-ul central din admin.php). Vezi issue #139.
require_once(__DIR__ . '/../csrf.php');
csrf_verify();

include_once("../../Database.php");

$id = (int) $_POST['id'];

// ---------------------------------------------------------------------------
// Validarea valorilor primite
// ---------------------------------------------------------------------------
$loop = isset($_POST['cron_loop']) ? (int) $_POST['cron_loop'] : 300;
$tick = isset($_POST['cron_tick']) ? (int) $_POST['cron_tick'] : 60;

// 0 = o singura rulare per invocare (host care permite cron la fiecare minut).
// Peste 0, limitam la 55 de minute ca sa nu ramana procese agatate.
if ($loop < 0)    { $loop = 0; }
if ($loop > 3300) { $loop = 3300; }

// Automation se asteapta sa ruleze pe la 60s; sub 15s doar incarcam serverul.
if ($tick < 15)  { $tick = 15; }
if ($tick > 900) { $tick = 900; }

// Un ciclu care nu incape nici macar un tick nu are sens - il tratam ca "o
// singura rulare", ca sa nu iasa o configuratie care pare activa dar nu e.
if ($loop > 0 && $loop < $tick) {
    $loop = 0;
}

// Retentiile de curatenie (0 = regula dezactivata). Limita superioara de 3650
// zile (10 ani) e doar ca sa nu se scrie valori absurde in config.
$cleanReports  = isset($_POST['cleanup_reports'])  ? (int) $_POST['cleanup_reports']  : 14;
$cleanChat     = isset($_POST['cleanup_chat'])     ? (int) $_POST['cleanup_chat']     : 7;
$cleanMessages = isset($_POST['cleanup_messages']) ? (int) $_POST['cleanup_messages'] : 0;

foreach (array('cleanReports', 'cleanChat', 'cleanMessages') as $var) {
    if ($$var < 0)    { $$var = 0; }
    if ($$var > 3650) { $$var = 3650; }
}

$regenerateKey = !empty($_POST['regenerate_key']);

$cronKey = (defined('CRON_KEY') && CRON_KEY !== '' && CRON_KEY !== '%CRONKEY%')
    ? CRON_KEY
    : '';

if ($regenerateKey || $cronKey === '') {
    $cronKey = bin2hex(random_bytes(24));
}

// ---------------------------------------------------------------------------
// Editarea chirurgicala a fisierului de configuratie
// ---------------------------------------------------------------------------
$configFile = "../../config.php";
$cronService = new \App\Admin\Services\CronSettingsService($database, TB_PREFIX, $configFile);
try {
    $cronService->updateCronSettings([
        'loop'          => $loop,
        'tick'          => $tick,
        'cronKey'       => $cronKey,
        'cleanReports'  => $cleanReports,
        'cleanChat'     => $cleanChat,
        'cleanMessages' => $cleanMessages,
    ]);
} catch (\Exception $e) {
    die("<br/><br/><br/>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

$cronService->logAdminAction($id, 'Changed Cron, Automation & Cleanup Settings');

header("Location: ../../../Admin/admin.php?p=config");
