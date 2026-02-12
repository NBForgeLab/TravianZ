<?php


include_once("../../Account.php");
if (!isset($_SESSION)) session_start();
if($_SESSION['access'] < ADMIN) die("Access Denied: You are not Admin!");



$deleteweek = (int) $_POST['medalweek'];
mysqli_query($GLOBALS["link"], "DELETE FROM ".TB_PREFIX."medal WHERE week = ".$deleteweek."");

header("Location: ../../../Admin/admin.php?p=delmedal");
?>