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
}
