<?php
namespace Tests;

require_once __DIR__ . '/../DatabaseTestCase.php';

class ArtifactsTest extends DatabaseTestCase
{
    public function testArtifactsInitialization()
    {
        // Use the global database initialized in DatabaseTestCase
        $artifacts = new \Artifacts($GLOBALS['database']);
        $this->assertInstanceOf(\Artifacts::class, $artifacts);
    }
}
