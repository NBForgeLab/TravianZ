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

$session = (int) $_POST['admid'];
$id = (int) $_POST['did'];

$rows = $database->query_return("SELECT access FROM ".TB_PREFIX."users WHERE id = ".(int)$session." LIMIT 1");
$sessionaccess = isset($rows[0]['access']) ? (int)$rows[0]['access'] : 0;

if($sessionaccess != 9) die("<h1><font color=\"red\">Access Denied: You are not Admin!</font></h1>");

$database->query("UPDATE ".TB_PREFIX."vdata SET wood = '".(int) $_POST['wood']."', clay = '".(int) $_POST['clay']."', iron = '".(int) $_POST['iron']."', crop = '".(int) $_POST['crop']."', maxstore = '".(int) $_POST['maxstore']."', maxcrop = '".(int) $_POST['maxcrop']."' WHERE wref = '".(int)$id."'");

header("Location: ../../../Admin/admin.php?p=village&did=".$id."");
?>
