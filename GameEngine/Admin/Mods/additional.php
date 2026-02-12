<?php

include_once("../../config.php");
include_once("../../Database.php");
if (!isset($_SESSION)) session_start();
if($_SESSION['access'] < 9) die("Access Denied: You are not Admin!");

$id = (int) $_POST['id'];
$admid = $_POST['admid'];

//$sql = mysqli_query($GLOBALS["link"], "SELECT * FROM ".TB_PREFIX."users WHERE id = ".$admid."");
//$access = mysqli_fetch_array($sql);
//$sessionaccess = $access['access'];
if (!isset($_SESSION)) {
 session_start();
}

if($_SESSION['access'] != ADMIN) die("<h1><font color=\"red\">Access Denied: You are not Admin!</font></h1>");

foreach ($_POST as $key => $value) {
    $_POST[$key] = $database->escape($value);
}

$access = (int) $_POST['access'];
$dur = (int) $_POST['protect'] * 86400;
$protection = (time() + $dur);

$database->query("UPDATE ".TB_PREFIX."users SET 
	access = ".$access.",
	gold = ".(int) $_POST['gold'].",	
	sit1 = '".(int) $_POST['sitter1']."',
	sit2 = '".(int) $_POST['sitter2']."',
	protect = '".$protection."',
	cp = ".(int) $_POST['cp'].",
	ap = '".(int) $_POST['off']."', 
	dp = '".(int) $_POST['def']."', 
	RR = '".(int) $_POST['res']."', 
	apall = '".(int) $_POST['ooff']."', 
	dpall = '".(int) $_POST['odef']."' 
	WHERE id = ".(int)$id);

header("Location: ../../../Admin/admin.php?p=player&uid=".$id."");
?>
