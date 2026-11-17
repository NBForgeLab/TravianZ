<?php
// tests/bootstrap.php

// Define fallback constants
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__ . '/../');
}

// Include autoloader
require_once ROOT_PATH . 'vendor/autoload.php';
