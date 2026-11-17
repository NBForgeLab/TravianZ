<?php
namespace Tests;

require_once __DIR__ . '/../DatabaseTestCase.php';

class ArtifactsTest extends DatabaseTestCase
{
    public function testArtifactsInitialization()
    {
        // Use the global database initialized in DatabaseTestCase
        $config = $this->createMock(\App\GameEngine\IConfig::class);
        $artifacts = new \Artifacts($GLOBALS['database'], $config);
        $this->assertInstanceOf(\Artifacts::class, $artifacts);
    }
}
