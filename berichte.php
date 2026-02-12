<?php
include_once("GameEngine/Generator.php");
$start_timer = $generator->pageLoadTimeStart();



use App\Service\ReportService;
use App\Utils\AccessLogger;
use App\View\ViewRenderer;

include_once("GameEngine/Village.php");
AccessLogger::logRequest();

$reportService = new ReportService($database, $session);
$reportService->initializeLegacyMessage($message, $_GET);
if (!$reportService->tryHandlePost($_POST)) {
	$message->procNotice($_POST);
}

$view = ViewRenderer::fromProjectRoot();
$viewContext = [
	'session' => $session,
	'database' => $database,
	'message' => $message,
	'generator' => $generator,
	'village' => $village ?? null,
	'building' => $building ?? null,
	'phpSession' => $_SESSION,
	'gameSession' => $session,
	'messageObj' => $message,
];
if(isset($_GET['newdid'])) {
	$_SESSION['wid'] = $_GET['newdid'];
    if ( isset( $_GET['t'] ) ) {
        header( "Location: " . $_SERVER['PHP_SELF'] . "?t=" . $_GET['t'] );
        exit;
    } else if ( isset( $_GET['vill'] ) && isset( $_GET['id'] ) ) {
        header( "Location: " . $_SERVER['PHP_SELF'] . "?id=" . $_GET['id'] . "&vill=" . $_GET['vill'] . "" );
        exit;
    } else if ( $_GET['id'] != 0 ) {
        header( "Location: " . $_SERVER['PHP_SELF'] . "?id=" . $_GET['id'] );
        exit;
    } else {
        header( "Location: " . $_SERVER['PHP_SELF'] );
        exit;
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title><?php echo SERVER_NAME ?> - Reports</title>
	<link rel="shortcut icon" href="favicon.ico"/>
	<meta http-equiv="cache-control" content="max-age=0" />
	<meta http-equiv="pragma" content="no-cache" />
	<meta http-equiv="expires" content="0" />
	<meta http-equiv="imagetoolbar" content="no" />
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<script src="unx.js?f4b7h" type="text/javascript" <?php echo trz_csp_nonce_attr(); ?>></script>
	<script src="new.js?0faab" type="text/javascript" <?php echo trz_csp_nonce_attr(); ?>></script>
	<link href="<?php echo GP_LOCATE; ?>lang/en/lang.css?f4b7d" rel="stylesheet" type="text/css" />
	<link href="<?php echo GP_LOCATE; ?>lang/en/compact.css?f4b7i" rel="stylesheet" type="text/css" />
	<?php
	if($session->gpack == null || GP_ENABLE == false) {
	echo "
	<link href='".GP_LOCATE."travian.css?e21d2' rel='stylesheet' type='text/css' />
	<link href='".GP_LOCATE."lang/en/lang.css?e21d2' rel='stylesheet' type='text/css' />";
	} else {
	echo "
	<link href='".$session->gpack."travian.css?e21d2' rel='stylesheet' type='text/css' />
	<link href='".$session->gpack."lang/en/lang.css?e21d2' rel='stylesheet' type='text/css' />";
	}
	?>
</head>


<body class="v35 ie ie8">
<div class="wrapper">
<img style="filter:chroma();" src="img/x.gif" id="msfilter" alt="" />
<div id="dynamic_header">
	</div>
<?php $view->displayPhp('Templates/header.tpl', $viewContext); ?>
<div id="mid">
<?php $view->displayPhp('Templates/menu.tpl', $viewContext); ?>
		<div id="content"  class="reports">
<h1>Reports</h1>
<div id="textmenu">
   <a href="berichte.php" <?php if (!isset($_GET['t'])) { echo "class=\"selected \""; } ?>>All</a>
 | <a href="berichte.php?t=2" <?php if (isset($_GET['t']) && $_GET['t'] == 2) { echo "class=\"selected \""; } ?>>Trade</a>
 | <a href="berichte.php?t=1" <?php if (isset($_GET['t']) && $_GET['t'] == 1) { echo "class=\"selected \""; } ?>>Reinforcement</a>
 | <a href="berichte.php?t=3" <?php if (isset($_GET['t']) && $_GET['t'] == 3) { echo "class=\"selected \""; } ?>>Attacks</a>
 | <a href="berichte.php?t=4" <?php if (isset($_GET['t']) && $_GET['t'] == 4) { echo "class=\"selected \""; } ?>>Miscellaneous</a>
 <?php if($session->plus) {
 echo "| <a href=\"berichte.php?t=5\"";
 if (isset($_GET['t']) && $_GET['t'] == 5) { echo "class=\"selected \""; }
 echo ">Archive</a>";
 }
 ?>
</div>
<?php
if (isset($_GET['id'])) 
{
    if (isset($_GET['aid']) && $_GET['aid'] > 0 && $_GET['aid'] == $session->alliance && $database->getNotice2($_GET['id'], 'ally') == $session->alliance)
    {
        $type = $database->getNotice2($_GET['id'], 'ntype');
        if ($type >= 10 && $type <= 17) unset($type);
    }
    elseif(isset($_GET['vill']) && $database->getNotice2($_GET['id'], 'ally') == $session->alliance)
    {
        $type = $database->getNotice2($_GET['id'], 'ntype');
        if ($type >= 10 && $type <= 17) unset($type);
    }
    elseif($database->getNotice2(preg_replace("/[^a-zA-Z0-9_-]/", "", $_GET['id']), 'uid') == $session->uid) 
    {
        $type = ($message->readingNotice['ntype'] == 9) ? $message->readingNotice['archive'] : $message->readingNotice['ntype'];
    }
    
    if(isset($type)) {
        $view->displayPhp('Templates/Notice/' . ReportService::mapReportType((int) $type) . '.tpl', $viewContext);
    }
    unset($type);
}
else {
    $view->displayPhp('Templates/Notice/all.tpl', $viewContext);
}
?>
</div>

<br /><br /><br /><br /><div id="side_info">
<?php
$view->displayPhp('Templates/multivillage.tpl', $viewContext);
$view->displayPhp('Templates/quest.tpl', $viewContext);
$view->displayPhp('Templates/news.tpl', $viewContext);
if(!NEW_FUNCTIONS_DISPLAY_LINKS) {
	echo "<br><br><br><br>";
	$view->displayPhp('Templates/links.tpl', $viewContext);
}
?>
</div>
<div class="clear"></div>
</div>
<div class="footer-stopper"></div>
<div class="clear"></div>

<?php
$view->displayPhp('Templates/footer.tpl', $viewContext);
$view->displayPhp('Templates/res.tpl', $viewContext);
?>
<div id="stime">
<div id="ltime">
<div id="ltimeWrap">
<?php echo CALCULATED_IN;?> <b><?php
echo round(($generator->pageLoadTimeEnd()-$start_timer)*1000);
?></b> ms

<br /><?php echo SERVER_TIME;?> <span id="tp1" class="b"><?php echo date('H:i:s'); ?></span>
</div>
	</div>
</div>

<div id="ce"></div>
</body>
</html>
