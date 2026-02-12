<?php
declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$config = new PhpCsFixer\Config();
return $config
    ->setRiskyAllowed(true)
    ->setUsingCache(true)
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'blank_line_after_opening_tag' => true,
        'no_unused_imports' => true,
        'ordered_imports' => true,
        'single_quote' => true,
        'strict_param' => true
    ])
    ->setFinder($finder);
