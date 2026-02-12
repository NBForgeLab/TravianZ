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

$rows = $database->query_return("SELECT id FROM ".TB_PREFIX."users ORDER BY id DESC LIMIT 1");
$loops = isset($rows[0]['id']) ? (int)$rows[0]['id'] : 0;

$plusdur = (int) ($_POST['plus'] ?? 0) * 86400;

for($i = 0; $i < $loops + 1; $i++)
{
    $userRows = $database->query_return("SELECT id, plus FROM ".TB_PREFIX."users WHERE id = ".(int)$i);
    foreach ($userRows as $row) {
        $plusbefore = ($row['plus'] < time()) ? time() : (int)$row['plus'];
        $addplus = $plusbefore + $plusdur;
        $database->query("UPDATE ".TB_PREFIX."users SET plus = '".$addplus."' WHERE id = '".(int)$row['id']."'");
    }
}

header("Location: ../../../Admin/admin.php?p=givePlus&g");
?>
