<?php
namespace Tests;

use PHPUnit\Framework\TestCase;

abstract class DatabaseTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // If not initialized, create a mock to allow tests to run
        if (!isset($GLOBALS['database'])) {
            $GLOBALS['database'] = $this->createMock(\MYSQLi_DB::class);
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
