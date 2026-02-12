<?php
if (!isset($_SESSION)) session_start();
if($_SESSION['access'] < 9) die("Access Denied: You are not Admin!");
include_once("../../config.php");

// go max 5 levels up - we don't have folders that go deeper than that
$autoprefix = '';
for ($i = 0; $i < 5; $i++) {
    $autoprefix = str_repeat('../', $i);
    if (file_exists($autoprefix.'autoloader.php')) {
        // we have our path, let's leave
        break;
    }
}

include_once($autoprefix."GameEngine/Database.php");

$session = isset($_POST['admid']) ? (int) $_POST['admid'] : 0;
$id = isset($_POST['uid']) ? (int) $_POST['uid'] : 0;
$newpw = isset($_POST['newpw']) ? (string) $_POST['newpw'] : '';
$pass = trz_password_hash($newpw);
$rows = $database->query_return("SELECT access FROM ".TB_PREFIX."users WHERE id = ".(int)$session." LIMIT 1");
$sessionaccess = isset($rows[0]['access']) ? (int)$rows[0]['access'] : 0;

if($sessionaccess != 9) die("<h1><font color=\"red\">Access Denied: You are not Admin!</font></h1>");
$database->query("UPDATE ".TB_PREFIX."users SET password = '".$database->escape($pass)."' WHERE id = ".(int)$id." LIMIT 1");

header("Location: ../../../Admin/admin.php?p=player&uid=".$id."");
?>
