<?php
namespace Tests;

use PHPUnit\Framework\TestCase;

abstract class DatabaseTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (!isset($GLOBALS['database'])) {
             require_once __DIR__ . '/../GameEngine/config.test.php';
             $GLOBALS['database'] = new \MYSQLi_DB(SQL_SERVER, SQL_USER, SQL_PASS, SQL_DB, 3306);
        }
        $GLOBALS['database']->query("START TRANSACTION");
    }

    protected function tearDown(): void
    {
        if (isset($GLOBALS['database'])) {
            $GLOBALS['database']->query("ROLLBACK");
        }
        parent::tearDown();
    }
}
