<?php
namespace Tests\GameEngine;

require_once __DIR__ . '/../DatabaseTestCase.php';

class DatabaseTest extends \Tests\DatabaseTestCase
{
    private $database;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = $GLOBALS['database'];
    }

    public function testIsConnected()
    {
        $this->assertTrue($this->database->is_connected());
    }

    public function testEscape()
    {
        $unsafe = "'; DROP TABLE users; --";
        $escaped = $this->database->escape($unsafe);
        // We expect it to be escaped.
        $this->assertNotEquals($unsafe, $escaped);
        $this->assertStringContainsString("\\'", $escaped);
    }

    public function testEscapeInput()
    {
        $unsafe = "'; DROP TABLE users; --";
        $results = $this->database->escape_input($unsafe, 123);
        $this->assertCount(2, $results);
        $this->assertNotEquals($unsafe, $results[0]);
        $this->assertEquals(123, $results[1]);
    }

    public function testQueryCountIncrements()
    {
        $reflection = new \ReflectionClass(\MYSQLi_DB::class);
        $property = $reflection->getProperty('selectQueryCount');
        $property->setAccessible(true);
        $initialCount = $property->getValue($this->database);
        
        $this->database->query("SELECT 1");
        
        $this->assertEquals($initialCount + 1, $property->getValue($this->database));
    }

    public function testQueryNewSupportsSingleScalarParameter()
    {
        $rows = $this->database->query_new("SELECT ? AS num", 42);
        $this->assertIsArray($rows);
        $this->assertCount(1, $rows);
        $this->assertEquals(42, (int)$rows[0]['num']);
    }

    public function testNumRowsReturnsRowCountForResult()
    {
        $res = $this->database->query("SELECT 1 AS a UNION ALL SELECT 2 AS a");
        $this->assertEquals(2, $this->database->num_rows($res));
        $this->assertEquals(0, $this->database->num_rows(false));
    }
}
