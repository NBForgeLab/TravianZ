<?php
namespace Tests;

use PHPUnit\Framework\TestCase;

abstract class DatabaseTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Force the use of the real database initialized in bootstrap.php
        $this->assertNotNull($GLOBALS['database'], "Database not initialized. Ensure bootstrap.php is loaded.");
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
