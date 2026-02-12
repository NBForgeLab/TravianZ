<?php

declare(strict_types=1);

namespace TravianZ\Tests\AdminE2E;

use mysqli;
use RuntimeException;

const ROOT_DIR = __DIR__ . '/../../..';
const ADMIN_CONFIG_PATH = ROOT_DIR . '/GameEngine/config.php';

require __DIR__ . '/TestRunner.php';
require __DIR__ . '/helpers.php';

chdir(ROOT_DIR);

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once ADMIN_CONFIG_PATH;

if (!defined('SQL_PORT')) {
    define('SQL_PORT', 3306);
}

function db(): mysqli
{
    static $db = null;
    if ($db instanceof mysqli) {
        return $db;
    }

    $db = new mysqli(SQL_SERVER, SQL_USER, SQL_PASS, SQL_DB, (int) SQL_PORT);
    if ($db->connect_errno) {
        throw new RuntimeException('DB connect failed: ' . $db->connect_error);
    }
    $db->set_charset('utf8mb4');

    return $db;
}

