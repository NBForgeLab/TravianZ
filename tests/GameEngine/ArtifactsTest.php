<?php
namespace Tests;

use PHPUnit\Framework\TestCase;

class ArtifactsTest extends TestCase
{
    public function testArtifactsInitializationAndConstants()
    {
        $db = $this->createMock(\MYSQLi_DB::class);
        $config = $this->createMock(\App\GameEngine\IConfig::class);
        $artifacts = new \Artifacts($db, $config);
        $this->assertInstanceOf(\Artifacts::class, $artifacts);
        $this->assertSame(3, \Artifacts::NATARS_UID);
    }
}
