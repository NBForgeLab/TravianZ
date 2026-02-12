<?php
include_once("GameEngine/Generator.php");
$start_timer = $generator->pageLoadTimeStart();




use App\Service\MessageService;
use App\Utils\AccessLogger;
use App\View\ViewRenderer;

include_once( "GameEngine/Village.php" );
AccessLogger::logRequest();

$messageService = new MessageService($database, $session);
if (!$messageService->tryHandlePost($_POST)) {
	$message->procMessage($_POST);
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

if(isset($_GET['newdid'])){
	$_SESSION['wid'] = $_GET['newdid'];
	if(isset($_GET['t'])){
		header("Location: ".$_SERVER['PHP_SELF']."?t=".$_GET['t']);
		exit();
	}else if($_GET['id'] != 0){
		header("Location: ".$_SERVER['PHP_SELF']."?id=".$_GET['id']);
		exit();
	}else{
		header("Location: ".$_SERVER['PHP_SELF']);
		exit();
	}
}

if(isset($_GET['delfriend']) && is_numeric($_GET['delfriend'])){
	$friend = $database->getUserField($session->uid, "friend".$_GET['delfriend'], 0);
	
	for($i = 0; $i <= 19; $i++){
		$friend1 = $database->getUserField($friend, "friend".$i, 0);
		if($friend1 == $session->uid){
			$database->deleteFriend($friend, "friend".$i);
		}
		$friendwait1 = $database->getUserField($friend, "friend".$i."wait", 0);
		if($friendwait1 == $session->uid){
			$database->deleteFriend($friend, "friend".$i."wait");
		}
		$database->checkFriends($friend);
	}
	
	$database->deleteFriend($session->uid, "friend".$_GET['delfriend']);
	$database->deleteFriend($session->uid, "friend".$_GET['delfriend']."wait");
	$database->checkFriends($session->uid);
	header("Location: ".$_SERVER['PHP_SELF']."?t=1");
	exit();
}

if(isset($_GET['confirm']) && is_numeric($_GET['confirm'])){
	$myid = $database->getUserArray($session->uid, 1);
	$wait = $database->getUserArray($myid['friend'.$_GET['confirm'].'wait'], 1);
	$added = 0;
	
	for($i = 0; $i < 20; $i++){
		$user = $database->getUserField($wait['id'], "friend".$i, 0);
		if($user == $session->uid && $added == 0){
			$database->addFriend($wait['id'], "friend".$i."wait", 0);
			$added = 1;
		}
	}
	
	$database->addFriend($session->uid, "friend".$_GET['confirm'], $wait['id']);
	$database->addFriend($session->uid, "friend".$_GET['confirm']."wait", 0);
	header("Location: ".$_SERVER['PHP_SELF']."?t=1");
	exit();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title><?php echo SERVER_NAME ?> - Messages</title>
   <link rel="shortcut icon" href="favicon.ico"/>
	<meta http-equiv="cache-control" content="max-age=0" />
	<meta http-equiv="pragma" content="no-cache" />
	<meta http-equiv="expires" content="0" />
	<meta http-equiv="imagetoolbar" content="no" />
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta name="X-UA-Compatible" content="IE=8" />
	<script src="unx.js?f4b7d" type="text/javascript" <?php echo trz_csp_nonce_attr(); ?>></script>
	<script src="new.js?f4b7d" type="text/javascript" <?php echo trz_csp_nonce_attr(); ?>></script>
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
<?php $view->displayPhp('Templates/menu.tpl', $viewContext);
if(isset($_GET['id']) && (!isset($_GET['t']) || $_GET['t'] == '2a')) {
	$message->loadMessage((int) $_GET['id']);
	$view->displayPhp('Templates/Message/read.tpl', $viewContext);
}
else if(isset($_GET['t'])) {
		switch((string) $_GET['t']) {
		case 1:
		if(isset($_GET['id'])) {
		    $id = preg_replace("/[^a-zA-Z0-9_-]/","",$_GET['id']);
		}
		$view->displayPhp('Templates/Message/write.tpl', $viewContext);
		break;
		case 2:
		$view->displayPhp('Templates/Message/sent.tpl', $viewContext);
		break;
		case 3:
		if($session->plus) {
			$view->displayPhp('Templates/Message/archive.tpl', $viewContext);
		}
		break;
		case 4:
		if($session->plus) {
			$message->loadNotes();
			$view->displayPhp('Templates/Message/notes.tpl', $viewContext);
		}
		break;
		default:
		$view->displayPhp('Templates/Message/inbox.tpl', $viewContext);
		break;
	}
}
else {
	$view->displayPhp('Templates/Message/inbox.tpl', $viewContext);
}
			?>

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
