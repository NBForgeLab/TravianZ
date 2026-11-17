<?php
namespace Tests\GameEngine;

require_once __DIR__ . '/../DatabaseTestCase.php';
require_once __DIR__ . '/../../GameEngine/Generator.php';

use Tests\DatabaseTestCase;
use MyGenerator;

class GeneratorTest extends DatabaseTestCase
{
    private $generator;

    protected function setUp(): void
    {
        parent::setUp();
        // Define constants if not already defined
        if (!defined('WORLD_MAX')) define('WORLD_MAX', 400);
        if (!defined('INCREASE_SPEED')) define('INCREASE_SPEED', 1);
        if (!defined('TS_THRESHOLD')) define('TS_THRESHOLD', 20);

        // Inject dependencies
        $this->generator = new MyGenerator($GLOBALS['database'], null, null, [
            'bid14' => [1 => ['attri' => 150]] // Example config
        ]);
    }

    public function testGenerateRandID()
    {
        $id = $this->generator->generateRandID();
        $this->assertEquals(32, strlen($id)); // MD5 hash length
    }

    public function testProcDistanceTime()
    {
        $coor = ['x' => 10, 'y' => 10];
        $thiscoor = ['x' => 20, 'y' => 20];
        // sqrt(10^2 + 10^2) = sqrt(200) = 14.14
        // Mode 0, Ref 1 (Speed 16)
        // 14.14 / 16 = 0.88375 hours = 3181.5 seconds = 3182
        $time = $this->generator->procDistanceTime($coor, $thiscoor, 1, 0);
        $this->assertEquals(3182, $time);
    }
}
