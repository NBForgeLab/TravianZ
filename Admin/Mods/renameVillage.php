<?php

include_once("../../Account.php");

if (!isset($_SESSION)) session_start();
if($_SESSION['access'] < ADMIN) die("Access Denied: You are not Admin!");

$origname = $_POST['villagename'];

foreach ($_POST as $key => $value) {
    $_POST[$key] = $database->escape($value);
}

$did = (int) $_POST['did'];
$name = $_POST['villagename'];
$sql = "UPDATE ".TB_PREFIX."vdata SET name = '$name' WHERE wref = $did";

mysqli_query($GLOBALS["link"], $sql);

header("Location: ../../../Admin/admin.php?p=village&did=".$did."&name=".$origname."");
?>