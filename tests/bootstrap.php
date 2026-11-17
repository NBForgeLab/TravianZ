<?php
namespace App\Utils {
    class Response {
        public static function redirect($url) {}
    }
}

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

    $_SERVER['REQUEST_URI'] = '/';

    // Stub Session class
    if (!class_exists('Session')) {
        class Session {
            public $tribe = 1;
            public $plus = false;
            public function getJobs() { return []; }
            public function sitterCan($perm) { return true; }
            public function getID() { return 1; }
        }
    }

    // Include config (ensure constants defined here don't conflict or are defined if not present)
    require_once __DIR__ . '/../GameEngine/config.test.php';

    // Include autoloader
    require_once __DIR__ . '/../vendor/autoload.php';

    // Include language
    require_once __DIR__ . '/../GameEngine/Lang/en.php';

    require_once __DIR__ . '/../GameEngine/Database.php';
    require_once __DIR__ . '/../GameEngine/Ranking.php';

    // Reset database
    $mysqli = new mysqli(SQL_SERVER, SQL_USER, SQL_PASS, SQL_DB);
    if (!$mysqli->connect_error) {
        $mysqli->query("SET FOREIGN_KEY_CHECKS = 0");
        $result = $mysqli->query("SHOW TABLES");
        $tables = [];
        while ($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }
        foreach ($tables as $table) {
            $mysqli->query("DROP TABLE IF EXISTS `" . $table . "`");
        }
        $mysqli->query("SET FOREIGN_KEY_CHECKS = 1");

        $sql = file_get_contents(__DIR__ . '/../var/db/struct.sql');
        $sql = str_replace('%PREFIX%', SQL_PREFIX, $sql);
        
        // Execute multi_query and consume all results
        if ($mysqli->multi_query($sql)) {
            do {
                if ($result = $mysqli->store_result()) {
                    $result->free();
                }
            } while ($mysqli->more_results() && $mysqli->next_result());
        }
        $mysqli->close();
    }

    // Initialize real database connection
    $database = new \MYSQLi_DB(SQL_SERVER, SQL_USER, SQL_PASS, SQL_DB, (defined('SQL_PORT') ? SQL_PORT : 3306));
    $GLOBALS['database'] = $database;
    $GLOBALS['db'] = $database;
    $GLOBALS['link'] = $database->return_link();

    $GLOBALS['form'] = new class { public $valuearray = []; public function addError($a, $b) {} public function getErrors() { return []; } public function returnErrors() { return 0; } };
    $GLOBALS['village'] = new class { public $wid = 1; public $resarray = []; public $natar = 0; public $master = 0; public $allcrop = 1000; public $pop = 10; public $unitarray = ['u11' => 10, 'hero' => 1]; };
    $GLOBALS['unitsbytype'] = ['cavalry' => [], 'catapult' => [], 'ram' => []];
    $GLOBALS['session'] = new Session();
    $GLOBALS['ranking'] = new \Ranking();
    $GLOBALS['bid34'] = [];
    $GLOBALS['bid35'] = [];
}
