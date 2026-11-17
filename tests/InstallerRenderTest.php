<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

if (!defined('TRAVIANZ_TEST_MODE')) {
    define('TRAVIANZ_TEST_MODE', true);
}

// Mock web environment
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_METHOD'] = 'GET';

$markers = [
    0 => 'Before you start',
    1 => 'SERVER RELATED',
    2 => 'Create database structure',
    3 => 'Create world data',
    4 => 'Multihunter account',
    5 => 'Installation complete',
];

for ($step = 0; $step <= 5; $step++) {
    $_GET['s'] = $step;
    ob_start();
    include __DIR__ . '/../install/index.php';
    $output = ob_get_clean();

    if (empty($output)) {
        throw new RuntimeException("Empty output on step {$step}");
    }
    if (strpos($output, 'Fatal error') !== false || strpos($output, 'Parse error') !== false) {
        throw new RuntimeException("Error detected on step {$step}: " . substr($output, 0, 500));
    }
    if (!isset($markers[$step]) || strpos($output, $markers[$step]) === false) {
        throw new RuntimeException("Marker '{$markers[$step]}' missing from step {$step} rendered output");
    }
    echo "Step {$step} rendered successfully with marker '{$markers[$step]}' (" . strlen($output) . " bytes)\n";
}

echo "All installer steps render without error and all markers verified!\n";
