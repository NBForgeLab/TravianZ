<?php

include_once ("config.php");
include_once (__DIR__ . "/Lang/" . LANG . ".php");

function travianz_bbcode_try_require_autoloader()
{
	for ($i = 0; $i < 5; $i++) {
		$prefix = str_repeat('../', $i);
		$autoloader = $prefix . 'autoloader.php';
		if (file_exists($autoloader)) {
			require_once $autoloader;
			return true;
		}
	}
	return false;
}

global $database, $generator;
$input = isset($input) ? (string) $input : '';

if (class_exists(\App\Legacy\BbCodeRenderer::class) || travianz_bbcode_try_require_autoloader()) {
	$bbcoded = \App\Legacy\BbCodeRenderer::render($input, $database, $generator);
} else {
	$bbcoded = $input;
}
