<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php


include_once("GameEngine/config.php");
?>

<html>
	<head>
	<title><?php echo SERVER_NAME; ?> - Manual</title>
		<link rel="shortcut icon" href="favicon.ico"/>
	<meta name="content-language" content="en" />
	<meta http-equiv="cache-control" content="max-age=0" />
	<meta http-equiv="imagetoolbar" content="no" />
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<script src="unx.js?f4b7h" type="text/javascript"></script>
	<script src="new.js?0faab" type="text/javascript"></script>
	<link href="<?php echo GP_LOCATE; ?>lang/en/compact.css?f4b7i" rel="stylesheet" type="text/css" />
	<link href="<?php echo GP_LOCATE; ?>lang/en/lang.css?f4b7d" rel="stylesheet" type="text/css" />
	<link href="<?php echo GP_LOCATE; ?>travian.css?f4b7d" rel="stylesheet" type="text/css" />
		<link href="<?php echo GP_LOCATE; ?>lang/en/lang.css" rel="stylesheet" type="text/css" />
	   </head>
	<body class="manual">
<?php
$s = isset($_GET['s']) && ctype_digit((string) $_GET['s']) ? (string) $_GET['s'] : '0';
$typ = isset($_GET['typ']) && ctype_digit((string) $_GET['typ']) ? (string) $_GET['typ'] : null;
$gid = isset($_GET['gid']) ? preg_replace("/[^a-zA-Z0-9_-]/","",(string) $_GET['gid']) : null;

if($typ === null && $s === '0') {
	include("Templates/Manual/00.tpl");
}
else if ($typ === null && $s == 1) {
	include("Templates/Manual/00.tpl");
}
else if ($typ === null && $s == 2) {
	include("Templates/Manual/direct.tpl");
}
else if ($typ !== null && $typ == 5 && $s == 3) {
	include("Templates/Manual/medal.tpl");
}
else {
	if($gid !== null && $typ !== null) {
		include("Templates/Manual/".$typ.$gid.".tpl");
	}
	else {
		if($typ === null) {
			include("Templates/Manual/00.tpl");
		} else {
			if($typ == 4 && $s == 0) {
				$s = '1';
			}
			include("Templates/Manual/".$typ.preg_replace("/[^a-zA-Z0-9_-]/","",$s).".tpl");
		}
	}
}
?>
</body>

</html>
