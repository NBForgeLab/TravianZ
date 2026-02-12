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
$rows = $database->query_return("SELECT access FROM ".TB_PREFIX."users WHERE id = ".(int)$session." LIMIT 1");
$sessionaccess = isset($rows[0]['access']) ? (int)$rows[0]['access'] : 0;

if($sessionaccess != 9) die("<h1><font color=\"red\">Access Denied: You are not Admin!</font></h1>");

$uid = isset($_POST['uid']) ? (int) $_POST['uid'] : 0;
$topic = isset($_POST['topic']) ? $_POST['topic'] : '';
$message = isset($_POST['message']) ? $_POST['message'] : '';
$time = isset($_POST['time']) ? (int) $_POST['time'] : time();
$database->query_new("INSERT INTO ".TB_PREFIX."mdata (target, owner, topic, message, viewed, time) VALUES (?, 1, ?, ?, 0, ?)", $uid, $topic, $message, $time);

header("Location: ../../../Admin/admin.php?p=Newmessage&uid=".$uid."&msg=ok");
?>
