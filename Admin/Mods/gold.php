<?php

include_once("../../Account.php");
if (!isset($_SESSION)) session_start();
if($_SESSION['access'] < ADMIN) die("Access Denied: You are not Admin!");

$id = (int) $_POST['id'];
$gold = (int) $_POST['gold'];

$q = "UPDATE ".TB_PREFIX."users SET gold = gold + ".$gold." WHERE id != '0'";
mysqli_query($GLOBALS["link"], $q);
mysqli_query($GLOBALS["link"], "Insert into ".TB_PREFIX."admin_log values (0,$id,'Added <b>$gold</b> gold to all users',".time().")");


header("Location: ../../../Admin/admin.php?p=gold&g");
?>