<?php
define('SQL_SERVER', 'localhost');
define('SQL_USER', 'travianz_tester');
define('SQL_PASS', 'tester_password');
define('SQL_DB', 'travianz_test');
if (!defined('SQL_PREFIX')) define('SQL_PREFIX', 'tz_');
define('BASIC_MAX', 1);
define('INNER_MAX', 1);
define('PLUS_MAX', 1);
define('ALLOW_ALL_TRIBE', false);
define('WORLD_MAX', 400);
define('SITTER_PERM_REINF', 1);
define('SITTER_PERM_ATTACK', 2);
define('SITTER_PERM_RAID', 4);
if (!defined('SITTER_PERM_BUY')) define('SITTER_PERM_BUY', 8);
if (!defined('SITTER_PERM_GOLD')) define('SITTER_PERM_GOLD', 16);
if (!defined('SITTER_PERM_RES')) define('SITTER_PERM_RES', 32);
define('MULTIHUNTER', 8);
define('ADMIN', 9);
define('ADMIN_ALLOW_INCOMING_RAIDS', false);

// Added missing constants for tests
define('AUTO_DEL_INACTIVE', true);
define('UN_ACT_TIME', 86400);
if (!defined('TB_PREFIX')) define('TB_PREFIX', 'tz_');
define('ALLOW_BURST', false);
define('STORAGE_BASE', 800);
