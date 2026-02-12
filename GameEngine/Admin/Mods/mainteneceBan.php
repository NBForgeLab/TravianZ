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

foreach ($_POST as $key => $value) {
    $_POST[$key] = $database->escape($value);
}

$session = (int) $_POST['admid'];

$rows = $database->query_return("SELECT access FROM ".TB_PREFIX."users WHERE id = ".(int)$session." LIMIT 1");
$sessionaccess = isset($rows[0]['access']) ? (int)$rows[0]['access'] : 0;

if($sessionaccess != 9) die("<h1><font color=\"red\">Access Denied: You are not Admin!</font></h1>");

$usersRows = $database->query_return("SELECT Count(*) as Total FROM ".TB_PREFIX."users");
$users = isset($usersRows[0]['Total']) ? (int)$usersRows[0]['Total'] : 0;

$duration = (int) $_POST['duration'] * 3600;
$start = $_POST['start'];
$startts = strtotime($start);
$endts = $startts + $duration;
$reason = $_POST['reason'];
$admin = $session;
$active = '1';
$access = '2';

function mysqli_result($res, $row, $field=0) {
	$res->data_seek($row);
	$datarow = $res->fetch_array();
	return $datarow[$field];
}

$rows2 = $database->query_return("SELECT id FROM ".TB_PREFIX."users ORDER BY id DESC LIMIT 1");
$loops = isset($rows2[0]['id']) ? (int)$rows2[0]['id'] : 0;

for($i = 0; $i < $loops + 1; $i++)
{
    $userRows = $database->query_return("SELECT id, username FROM ".TB_PREFIX."users WHERE id = ".(int)$i." AND access = ".(int)$access);
    foreach ($userRows as $row)
    {
        $database->query("INSERT INTO ".TB_PREFIX."banlist VALUES('', ".(int) $row['id'].", '".$row['username']."', '".$reason."', ".(int) $startts.", ".(int) $endts.", ".(int) $admin.", ".(int) $active.")");
    }
}

header("Location: ../../../Admin/admin.php?p=ban");
?>
