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
$id = (int) $_POST['id'];

$rows = $database->query_return("SELECT access FROM ".TB_PREFIX."users WHERE id = ".(int)$session." LIMIT 1");
$sessionaccess = isset($rows[0]['access']) ? (int)$rows[0]['access'] : 0;

if($sessionaccess != 9) die("<h1><font color=\"red\">Access Denied: You are not Admin!</font></h1>");

$dur = (int) $_POST['protect'] * 86400;
$protection = (time() + $dur);

$database->query("UPDATE ".TB_PREFIX."users SET protect = '".$protection."' WHERE id = ".(int)$id);

header("Location: ../../../Admin/admin.php?p=player&uid=".$id."");
?>
