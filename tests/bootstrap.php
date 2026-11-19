<?php
namespace {
    if (!defined('USER')) define('USER', 2);
    if (!defined('START_DATE')) define('START_DATE', date('d.m.Y'));
    if (!defined('START_TIME')) define('START_TIME', date('H:i:s'));
    if (!defined('PROTECTION')) define('PROTECTION', 3600);
    if (!defined('TRAVIANZ_TEST_MODE')) define('TRAVIANZ_TEST_MODE', true);
    if (!defined('USRNM_MIN_LENGTH')) define('USRNM_MIN_LENGTH', 3);
    if (!defined('PW_MIN_LENGTH')) define('PW_MIN_LENGTH', 6);
    if (!defined('LANG')) define('LANG', 'en');
    if (!defined('TB_PREFIX')) define('TB_PREFIX', 'tz_');
    if (!defined('SQL_PREFIX')) define('SQL_PREFIX', 'tz_');
    if (!defined('SERVER_NAME')) define('SERVER_NAME', 'Test Server');
    if (!defined('SPEED')) define('SPEED', 1);
    if (!defined('COMMENCE')) define('COMMENCE', 0);
    if (!defined('TS_THRESHOLD')) define('TS_THRESHOLD', 0);
    if (!defined('CRANNY_CAPACITY')) define('CRANNY_CAPACITY', 100);
    if (!defined('MIN_TRP_TRAIN')) define('MIN_TRP_TRAIN', 1);

    $_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/';

    // Include autoloaders first so App\Utils\Response resolves from src/Utils/Response.php
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../autoloader.php';
    require_once __DIR__ . '/../src/Utils/Response.php';

    // Stub Session class when full GameEngine/Session.php has not been loaded
    if (!class_exists('Session', false)) {
        class Session {
            public $uid = 1;
            public $username = 'TestUser';
            public $tribe = 1;
            public $plus = false;
            public $alliance = 0;
            public $access = 2;
            public $isAdmin = false;
            public $checker = 'test_checker';
            public $cp = 0;
            public $userinfo = ['password' => '', 'sit1' => 0, 'sit2' => 0];
            public $villages = [1];
            public $sharedForums = [];
            public $hero = [];
            public $bonus1 = 0;
            public $bonus2 = 0;
            public $bonus3 = 0;
            public $bonus4 = 0;
            public function getJobs() { return []; }
            public function sitterCan($perm) { return true; }
            public function isSitterSession() { return false; }
            public function changeChecker() {}
            public function Logout() {}
            public function getID() { return $this->uid; }
        }
    }

    // Include config (ensure constants defined here don't conflict or are defined if not present)
    require_once __DIR__ . '/../GameEngine/config.test.php';

    // Include language
    require_once __DIR__ . '/../GameEngine/Lang/en.php';

    require_once __DIR__ . '/../GameEngine/Database.php';
    require_once __DIR__ . '/../GameEngine/Ranking.php';

    $dbHost = getenv('SQL_SERVER') ?: SQL_SERVER;
    $dbUser = getenv('SQL_USER') ?: SQL_USER;
    $dbPass = getenv('SQL_PASS') !== false ? getenv('SQL_PASS') : SQL_PASS;
    $dbName = getenv('SQL_DB') ?: SQL_DB;
    $dbPort = getenv('SQL_PORT') ? (int)getenv('SQL_PORT') : (defined('SQL_PORT') ? (int)SQL_PORT : 3306);

    // Initialize database if reachable; otherwise allow pure unit tests to run cleanly
    $dbAvailable = false;
    $database = null;
    $link = null;

    if (extension_loaded('mysqli')) {
        try {
            $probe = @new \mysqli($dbHost, $dbUser, $dbPass, '', $dbPort);
            if ($probe && !$probe->connect_errno) {
                $structPath = __DIR__ . '/../var/db/struct.sql';
                $schemaHash = is_file($structPath) ? substr(md5_file($structPath), 0, 12) : 'default';
                $initializedFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'travianz_test_initialized_' . $schemaHash;
                $dbCheck = @$probe->select_db($dbName);
                if (!file_exists($initializedFile) || !$dbCheck) {
                    $lockFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'travianz_test.lock';
                    $fp = @fopen($lockFile, 'c');
                    if ($fp && flock($fp, LOCK_EX)) {
                        if (!file_exists($initializedFile) || !@$probe->select_db($dbName)) {
                            $probe->query("DROP DATABASE IF EXISTS `" . $dbName . "`");
                            $probe->query("CREATE DATABASE IF NOT EXISTS `" . $dbName . "`");
                            $probe->select_db($dbName);

                            $sql = file_get_contents($structPath);
                            $sql = str_replace('%PREFIX%', SQL_PREFIX, $sql);

                            if ($probe->multi_query($sql)) {
                                do {
                                    if ($result = $probe->store_result()) {
                                        $result->free();
                                    }
                                } while ($probe->more_results() && $probe->next_result());
                            }
                            @touch($initializedFile);
                        }
                        flock($fp, LOCK_UN);
                    }
                    if ($fp) {
                        fclose($fp);
                    }
                }
                $probe->close();

                $database = new \MYSQLi_DB($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
                $link = $database->return_link();
                $dbAvailable = ($link instanceof \mysqli);
                if ($dbAvailable) {
                    @mysqli_query($link, "DELETE FROM `" . SQL_PREFIX . "users` WHERE id > 5");
                }
            }
        } catch (\Throwable $e) {
            $dbAvailable = false;
            $database = null;
            $link = null;
        }
    }

    if (!defined('TRAVIANZ_DB_AVAILABLE')) {
        define('TRAVIANZ_DB_AVAILABLE', $dbAvailable);
    }

    $GLOBALS['database'] = $database;
    $GLOBALS['db'] = $database;
    $GLOBALS['link'] = $link;

    // Define missing constants for tests
    if (!defined('NATARS_SPAWN_TIME')) define('NATARS_SPAWN_TIME', 86400);
    if (!defined('NATARS_WW_BUILD_TIME')) define('NATARS_WW_BUILD_TIME', 3600);
    if (!defined('NATURE_REGTIME')) define('NATURE_REGTIME', 3600);
    if (!defined('MEDALINTERVAL')) define('MEDALINTERVAL', 86400);
    if (!defined('SHOW_NATARS')) define('SHOW_NATARS', false);
    if (!defined('NATARS_UNITS')) define('NATARS_UNITS', 1);
    if (!defined('ALLOW_ALL_TRIBE')) define('ALLOW_ALL_TRIBE', false);
    if (!defined('BASIC_MAX')) define('BASIC_MAX', 1);
    if (!defined('INNER_MAX')) define('INNER_MAX', 1);
    if (!defined('PLUS_MAX')) define('PLUS_MAX', 1);
    if (!defined('INCLUDE_ADMIN')) define('INCLUDE_ADMIN', false);
    if (!defined('REG_OPEN')) define('REG_OPEN', true);
    if (!defined('MEDAL_TYPE')) define('MEDAL_TYPE', 0);
    if (!defined('MAX_LEVEL')) define('MAX_LEVEL', 20);
    if (!defined('STORAGE_MULTIPLIER')) define('STORAGE_MULTIPLIER', 1);
    if (!defined('NEW_FUNCTIONS_MILESTONES')) define('NEW_FUNCTIONS_MILESTONES', false);
    if (!defined('NEW_FUNCTIONS_HERO_T4')) define('NEW_FUNCTIONS_HERO_T4', false);

    $GLOBALS['form'] = new class { public $valuearray = []; public function addError($a, $b) {} public function getErrors() { return []; } public function returnErrors() { return 0; } };
    $GLOBALS['village'] = new class { public $wid = 1; public $resarray = []; public $natar = 0; public $master = 0; public $allcrop = 1000; public $pop = 10; public $unitarray = ['u11' => 10, 'hero' => 1]; };
    $GLOBALS['unitsbytype'] = ['cavalry' => [], 'catapult' => [], 'ram' => []];
    $GLOBALS['session'] = new Session();
    $GLOBALS['ranking'] = new \Ranking();
    $GLOBALS['bid34'] = [];
    $GLOBALS['bid35'] = [];
}
