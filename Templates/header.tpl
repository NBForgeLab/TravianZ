<?php
$phpSession = $phpSession ?? ($_SESSION ?? []);
$gameSession = $gameSession ?? ($session ?? null);
$messageObj = $messageObj ?? ($message ?? null);
?>

<div id="header">
    <div id="mtop">
        <a href="<?php echo (($phpSession['id_user'] ?? 1) != 1 ? 'dorf1.php' : '#'); ?>" id="n1" accesskey="1"><img src="img/x.gif" title="Village overview" alt="Village overview" /></a>
        <a href="<?php echo (($phpSession['id_user'] ?? 1) != 1 ? 'dorf2.php' : '#'); ?>" id="n2" accesskey="2"><img src="img/x.gif" title="Village centre" alt="Village centre" /></a>
        <a href="karte.php" id="n3" accesskey="3"><img src="img/x.gif" title="Map" alt="Map" /></a>
        <a href="statistiken.php" id="n4" accesskey="4"><img src="img/x.gif" title="Statistics" alt="Statistics" /></a>
        <?php
        if($messageObj && $messageObj->unread && !$messageObj->nunread) {
        $class = "i2";
        }
        else if($messageObj && !$messageObj->unread && $messageObj->nunread) {
        $class = "i3";
        }
        else if($messageObj && $messageObj->unread && $messageObj->nunread) {
        $class = "i1";
        }
        else {
        $class = "i4";
        }
        ?>
          <div id="n5" class="<?php echo $class ?>">
            <a href="<?php echo (($phpSession['id_user'] ?? 1) != 1 ? 'berichte.php' : '#'); ?>" accesskey="5"><img src="img/x.gif" class="l" title="Reports" alt="Reports"/></a>
            <a href="nachrichten.php" accesskey="6"><img src="img/x.gif" class="r" title="Messages" alt="Messages" /></a>
        </div>

		<?php
			// no PLUS needed for Support
			if (($phpSession['id_user'] ?? 1) != 1) {
		?>
        <a href="plus.php" id="plus">
        <span class="plus_text">
            <span class="plus_g">P</span>
            <span class="plus_o">l</span>
            <span class="plus_g">u</span>
            <span class="plus_o">s</span>
       </span><img src="img/x.gif" id="btn_plus" class="<?php echo ($gameSession && $gameSession->plus == 1 && strtotime("NOW") <= $gameSession->userinfo['plus'])? 'active' : 'inactive';?>" title="Plus menu" alt="Plus menu" /></a>
       <?php
       		}
       ?>
<?php $dayNightCssV = @filemtime(__DIR__ . '/../assets/css/header-daynight.css') ?: time(); ?>
<link rel="stylesheet" href="assets/css/header-daynight.css?v=<?php echo $dayNightCssV; ?>" />
<?php
$hour = date('Hi'); 
if ($hour > 1759 or $hour < 500) {
$day_night_img = 'night_image';
} elseif ($hour > 1200) {
$day_night_img = 'day_image';
} else {
$day_night_img = 'day_image';
}
?>
<div id="wrapper">
  <div id="container">
 <div><div><p><img src="img/x.gif" style="display: block; margin: 0 auto; vertical-align:middle;" class="<?php echo $day_night_img;?>"  /></p></div></div>
  </div>
</div>
        <div class="clear"></div>
    </div>
</div>
<?php $bootstrapVersion = @filemtime(__DIR__ . '/../assets/js/app/bootstrap.js') ?: time(); ?>
<script src="assets/js/app/bootstrap.js?v=<?php echo $bootstrapVersion; ?>" type="text/javascript" defer></script>
<?php
$twbsBootstrapVersion = '5.3.8';
$enableBootstrapCss = $enableBootstrapCss ?? false;

$twbsBootstrapCssPath = __DIR__ . '/../assets/vendor/bootstrap/' . $twbsBootstrapVersion . '/dist/css/bootstrap.min.css';
$twbsBootstrapJsPath = __DIR__ . '/../assets/vendor/bootstrap/' . $twbsBootstrapVersion . '/dist/js/bootstrap.bundle.min.js';
$twbsBootstrapCssV = @filemtime($twbsBootstrapCssPath) ?: time();
$twbsBootstrapJsV = @filemtime($twbsBootstrapJsPath) ?: time();
?>
<?php if ($enableBootstrapCss) { ?>
<link rel="stylesheet" href="assets/vendor/bootstrap/<?php echo $twbsBootstrapVersion; ?>/dist/css/bootstrap.min.css?v=<?php echo $twbsBootstrapCssV; ?>" />
<?php } ?>
<script src="assets/vendor/bootstrap/<?php echo $twbsBootstrapVersion; ?>/dist/js/bootstrap.bundle.min.js?v=<?php echo $twbsBootstrapJsV; ?>" type="text/javascript" defer></script>
