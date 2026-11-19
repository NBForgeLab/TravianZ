<?php
namespace Tests;

use PHPUnit\Framework\TestCase;

abstract class DatabaseTestCase extends TestCase
{
    private $realDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (!defined('TRAVIANZ_DB_AVAILABLE') || !TRAVIANZ_DB_AVAILABLE || !isset($GLOBALS['database'])) {
            $this->markTestSkipped('MySQL test database is not available.');
        }
        $this->realDatabase = $GLOBALS['database'];
        $this->realDatabase->query("START TRANSACTION");
    }

    protected function tearDown(): void
    {
        if ($this->realDatabase !== null) {
            $GLOBALS['database'] = $this->realDatabase;
            $this->realDatabase->query("ROLLBACK");
        }
        parent::tearDown();
    }
}
