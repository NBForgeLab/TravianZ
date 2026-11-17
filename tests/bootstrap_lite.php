<?php
namespace {
    if (!defined('USER')) define('USER', 2);
    // ... define other necessary constants
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../autoloader.php';
    require_once __DIR__ . '/../GameEngine/Database.php';
    
    // Mock database if needed
    $GLOBALS['database'] = new class {
        public function getUnreadMessagesCount($uid) { return 0; }
        public function getUnreadNoticesCount($uid) { return 0; }
        public function __call($name, $args) { return null; }
    };
    $GLOBALS['session'] = new class {
        public $uid = 1;
        public $alliance = 0;
        public $plus = false;
        public $access = 0;
    };
}
