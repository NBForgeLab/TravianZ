<?php
namespace App\Utils {
    class Response {
        public static function redirect($url) {}
    }
}

namespace {
    if (!defined('TRAVIANZ_TEST_MODE')) define('TRAVIANZ_TEST_MODE', true);
    if (!defined('USRNM_MIN_LENGTH')) define('USRNM_MIN_LENGTH', 3);
    if (!defined('PW_MIN_LENGTH')) define('PW_MIN_LENGTH', 6);
    if (!defined('LANG')) define('LANG', 'en');
    if (!defined('TB_PREFIX')) define('TB_PREFIX', 'tz_');
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
    if (!defined('SQL_SERVER')) {
        require_once __DIR__ . '/../GameEngine/config.php';
    }

    // Include autoloader
    require_once __DIR__ . '/../vendor/autoload.php';

    // Dummy Globals for refactored code that still relies on them
    class MockDb implements \App\Database\IDbConnection {
        public function connect(): bool { return true; }
        public function disconnect(): bool { return true; }
        public function reconnect(): bool { return true; }
        public function is_connected(): bool { return true; }
        public function query_new($statement, ...$params) { return null; }
        public function getJobs($wid) { return []; }
        public function __call($name, $args) { return null; }
        public function getArtifactsValueInfluence($a, $b, $c, $d, $e) { return 0; }
        public function getVillageField($a, $b) { return 0; }
        public function getHero($uid) { return []; }
        public function getResourceLevel($vid) { return []; }
        public function getBuildingByType($wid, $type) { return []; }
        public function getWWConstructionPlans($uid, $ally = 0) { return []; }
        public function getDemolition($wid) { return []; }
        public function getMasterJobs($wid) { return []; }
        public function getFieldLevel($wid, $fieldId) { return 0; }
        public function addBuilding($wid, $fieldId, $gid, $loop, $time, $master, $level) { return true; }
        public function getBuildingByField($wid, $fieldId) { return []; }
        public function getBuildLock($wid) { return true; }
        public function removeBuilding($d, $tribe, $wid, $resarray) { return true; }
        public function modifyResource($wid, $wood, $clay, $iron, $crop, $add) { return true; }
        public function hasBeginnerProtection($id) { return 0; }
        public function getVillage($id) { return ['owner' => 1, 'name' => 'Test']; }
        public function getOasisV($id) { return ['owner' => 1, 'name' => 'Test']; }
        public function getUserField($uid, $field, $default) { return 0; }
        public function getvacmodexy($id) { return false; }
        public function getEnforceVillage($wref, $mode, $cache) { return []; }
        public function getOasisEnforce($wref, $mode, $cache) { return []; }
        public function getOasisReturnLock($wref) { return true; }
        public function getOasisEnforceByWref($wref, $cache) { return []; }
        public function regenerateOasisUnits($wref) { return true; }
        public function releaseOasisReturnLock($wref) {}
        public function getEnforceLock($id) { return true; }
        public function getEnforceArray($id, $mode, $cache) { return []; }
        public function processReturnTroops($enforce) {}
        public function releaseEnforceLock($id) {}
        public function getWalkingTroopsTime($fromWref, $oasisWref, $ownerId, $tribe, $enforce, $mode) { return 100; }
        public function deleteReinf($id) {}
        public function addAttack($from, $t1, $t2, $t3, $t4, $t5, $t6, $t7, $t8, $t9, $t10, $hero, $type, $target, $mode, $x, $y) { return 1; }
        public function addMovement($type, $from, $to, $ref, $start, $end) {}
        public function getA2b($checksum) { return []; }
        public function claimA2b($id, $checksum) { return true; }
        public function modifyUnit($wid, $units, $add) {}
    }

    $GLOBALS['database'] = new MockDb();
    $GLOBALS['form'] = new class { public $valuearray = []; public function addError($a, $b) {} public function getErrors() { return []; } public function returnErrors() { return 0; } };
    $GLOBALS['village'] = new class { public $wid = 1; public $resarray = []; public $natar = 0; public $master = 0; public $allcrop = 1000; public $pop = 10; public $unitarray = ['u11' => 10, 'hero' => 1]; };
    $GLOBALS['unitsbytype'] = ['cavalry' => [], 'catapult' => [], 'ram' => []];
    $GLOBALS['session'] = new Session();
    $GLOBALS['bid34'] = [];
    $GLOBALS['bid35'] = [];
}
