<?php
############################################################
##                                                        ##
##     Test functions so far mini template parser         ##
##     Author : Advocaite                                 ##
##     Project : TravianZ                                 ##
##                                                        ##
############################################################

function travianz_try_require_autoloader()
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

function addSub($subName, $sub)
{
	$GLOBALS['subs']["{".$subName."}"] = $sub;
}

function template($filepath, $subs)
{
	if (class_exists(\App\Legacy\TemplateRenderer::class) || travianz_try_require_autoloader()) {
		try {
			return \App\Legacy\TemplateRenderer::renderFile($filepath, $subs);
		} catch (\RuntimeException $e) {
			print "File '$filepath' not found";
			return false;
		}
	}

	if(file_exists($filepath))
	{
		$text = file_get_contents($filepath);
	} else {
		print "File '$filepath' not found";
		return false;
	}

	foreach($subs as $sub => $repl)
	{
		$text = str_replace($sub, $repl, $text);
	}

	return $text;
}

function travianz_view_render(string $templatePath, array $context = []): string
{
	if (class_exists(\App\View\ViewRenderer::class) || travianz_try_require_autoloader()) {
		$renderer = \App\View\ViewRenderer::fromProjectRoot();
		return $renderer->renderPhp($templatePath, $context);
	}

	$projectRoot = dirname(__DIR__);
	$absolutePath = $projectRoot . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $templatePath);
	if (!file_exists($absolutePath)) {
		throw new RuntimeException("View not found: {$templatePath}");
	}

	if (!empty($context)) {
		extract($context, EXTR_SKIP);
	}

	ob_start();
	try {
		include $absolutePath;
	} finally {
		$output = ob_get_clean();
	}

	return $output === false ? '' : $output;
}

function travianz_view_display(string $templatePath, array $context = []): void
{
	echo travianz_view_render($templatePath, $context);
}

?>
