<?php
if (!isset($_SESSION)) session_start();
if($_SESSION['access'] < 9) die("Access Denied: You are not Admin!");
include_once("../../config.php");

function mysqli_result($res, $row, $field=0) {
	$res->data_seek($row);
	$datarow = $res->fetch_array();
	return $datarow[$field];
}

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

$rows2 = $database->query_return("SELECT id FROM ".TB_PREFIX."users ORDER BY id DESC LIMIT 1");
$loops = isset($rows2[0]['id']) ? (int)$rows2[0]['id'] : 0;

$wood = (int) $_POST['wood'] * 86400;
$clay = (int) $_POST['clay'] * 86400;
$iron = (int) $_POST['iron'] * 86400;
$crop = (int) $_POST['crop'] * 86400;

for($i = 0; $i < $loops + 1; $i++)
{
    $userRows = $database->query_return("SELECT id, b1, b2, b3, b4 FROM ".TB_PREFIX."users WHERE id = ".(int)$i);
    foreach ($userRows as $row)
    {
        $b1before = ($row['b1'] < time()) ? time() : (int)$row['b1'];
        $b2before = ($row['b2'] < time()) ? time() : (int)$row['b2'];
        $b3before = ($row['b3'] < time()) ? time() : (int)$row['b3'];
        $b4before = ($row['b4'] < time()) ? time() : (int)$row['b4'];
        $addb1 = $b1before + $wood;
        $addb2 = $b2before + $clay;
        $addb3 = $b3before + $iron;
        $addb4 = $b4before + $crop;
        $database->query("UPDATE ".TB_PREFIX."users SET b1 = '".$addb1."', b2 = '".$addb2."', b3 = '".$addb3."', b4 = '".$addb4."' WHERE id = '".(int)$row['id']."'");
    }
}

header("Location: ../../../Admin/admin.php?p=givePlusRes&g");
?>
