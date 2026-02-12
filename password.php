<?php

use App\Utils\AccessLogger;

if(!file_exists('var/installed') && @opendir('install')) {
	header("Location: install/");
	exit;
}
include_once("GameEngine/config.php");
include_once("GameEngine/Lang/" . LANG . ".php");
include_once("GameEngine/Database.php");
include_once("GameEngine/Mailer.php");
include_once("GameEngine/Generator.php");
AccessLogger::logRequest();

if(!isset($_REQUEST['npw'])){
	header("Location: login.php");
	exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['csrf']) || !isset($_POST['csrf']) || $_SESSION['csrf'] !== $_POST['csrf']) {
        throw new RuntimeException('CSRF attack');
    }
}
$key = trz_random_token(32);
$_SESSION['csrf'] = $key;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
	<title><?php echo SERVER_NAME; ?> - Forgotten Password</title>
		<link rel="shortcut icon" href="favicon.ico"/>
	<meta name="content-language" content="en" />
	<meta http-equiv="cache-control" content="max-age=0" />
	<meta http-equiv="imagetoolbar" content="no" />
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<script src="unx.js?f4b7h" type="text/javascript" <?php echo trz_csp_nonce_attr(); ?>></script>
	<script src="new.js?0faab" type="text/javascript" <?php echo trz_csp_nonce_attr(); ?>></script>
	<link href="<?php echo GP_LOCATE; ?>lang/en/compact.css?f4b7i" rel="stylesheet" type="text/css" />
	<link href="<?php echo GP_LOCATE; ?>lang/en/lang.css?f4b7d" rel="stylesheet" type="text/css" />
	<link href="<?php echo GP_LOCATE ?>travian.css?f4b7d" rel="stylesheet" type="text/css" />
		<link href="<?php echo GP_LOCATE ?>lang/en/lang.css" rel="stylesheet" type="text/css" />
	   </head>

<body class="v35 ie ie7">

<div class="wrapper">
<div id="dynamic_header">
</div>
<div id="header"></div>
<div id="mid">
<?php include("Templates/menu.tpl"); ?>
<div id="content"  class="activate">

		<h1><img src="img/x.gif" class="passwort" alt="new password" /></h1>
		<h5><img src="img/x.gif" class="img_u22" alt="forgotten password" /></h5>

<?php
	// user input email and submit
if(isset($_POST['email']) && isset($_POST['npw'])){
		$uid = intval($_POST['npw']);
		$email = $database->getUserField($uid, 'email', 0);
		$username = $database->getUserField($uid, 'username', 0);
        // Always respond generically to avoid user enumeration
        $token = $generator->generateRandStr(32);
        if ($email && $database->createPasswordResetToken($uid, $token, 3600)) {
            $mailer->sendPasswordResetLink($email, $uid, $username, $token);
        }
        echo "<p>If an account exists for the provided email, a password reset link has been sent.</p>\n";

	// user click the link in 'password forgotten' email
}else if(isset($_GET['action']) && $_GET['action'] === 'reset' && isset($_GET['uid']) && isset($_GET['token'])){
		$uid = intval($_GET['uid']);
		$token = preg_replace('#[^a-zA-Z0-9]#', '', $_GET['token']);
		if(!$database->verifyPasswordResetToken($uid, $token)){
			echo '<p>The reset link is invalid or has expired.</p>';
		}else{
			?>
			<form action="password.php" method="post">
				<p>
					<b>New Password</b><br />
					<input type="hidden" name="action" value="set_new" />
					<input type="hidden" name="uid" value="<?php echo (int)$uid; ?>" />
					<input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>" />
					<input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8'); ?>" />
					<input class="text" type="password" name="newpw" maxlength="64" />
				</p>
				<p>
					<button value="ok" name="s1" class="trav_buttons" id="btn_ok" alt="OK" /> set </button>
				</p>
			</form>
			<?php
		}
}else if(isset($_POST['action']) && $_POST['action'] === 'set_new' && isset($_POST['uid']) && isset($_POST['token']) && isset($_POST['newpw'])){
		$uid = intval($_POST['uid']);
		$token = preg_replace('#[^a-zA-Z0-9]#', '', $_POST['token']);
		$newpw = (string) $_POST['newpw'];
		if(strlen($newpw) < 6){
			echo '<p>Password too short.</p>';
		}else if(!$database->verifyPasswordResetToken($uid, $token)){
			echo '<p>The reset link is invalid or has expired.</p>';
		}else{
			if(!$database->updateUserField($uid, 'password', trz_password_hash($newpw), 1)){
				echo '<p>Could not update password.</p>';
			}else{
				$database->consumePasswordResetToken($uid, $token);
				echo '<p>The password has been successfully changed.</p>';
			}
		}


	// user click 'generate password' link in login fail page, display input form here
	}else {

?>
		<p>Before you can request a new password you have to enter the email address that has been used to register the account.
<br /><br />Afterwards you will receive an e-mail with a new password. The password will only work after confirming it, though.</p>
		<form action="password.php" method="post">
			<p>
				<b>Email</b><br />
				<input type="hidden" name="npw" value="<?php echo intval($_GET['npw'] ?? 0); ?>" />
                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8'); ?>" />
				<input class="text" type="email" name="email" maxlength="50" />
			</p>

			<p>
				<button value="ok" name="s1" class="trav_buttons" id="btn_ok" alt="OK" /> ok </button>
			</p>
		</form>
<?php
	}
?>
</div>
<div id="side_info" class="outgame">
</div>

<div class="clear"></div>
			</div>

			<div class="footer-stopper outgame"></div>
			<div class="clear"></div>

<?php include("Templates/footer.tpl"); ?>
<div id="ce"></div>
</body>
</html>
