<?php
namespace Tests\Admin\Services;

require_once __DIR__ . '/../../DatabaseTestCase.php';

use App\Admin\Services\HeroSettingsService;

class HeroSettingsServiceTest extends \Tests\DatabaseTestCase
{
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new HeroSettingsService($GLOBALS['database'], TB_PREFIX);
    }

    public function testUpdateHero()
    {
        // Setup a dummy hero
        $GLOBALS['database']->query("INSERT INTO " . TB_PREFIX . "hero (heroid, uid, name) VALUES (1, 1, 'OldName')");
        
        $data = [
            'unit' => 1,
            'name' => 'NewName',
            'level' => 10,
            'points' => 100,
            'experience' => 1000,
            'health' => 90.0,
            'attack' => 5,
            'defence' => 5,
            'attackbonus' => 1,
            'defencebonus' => 1,
            'regeneration' => 2,
            'resources' => 0,
            'res_type' => 0
        ];
        
        $this->service->updateHero(1, 1, $data);
        
        $result = $GLOBALS['database']->query("SELECT name FROM " . TB_PREFIX . "hero WHERE heroid = 1 AND uid = 1");
        $row = mysqli_fetch_assoc($result);
        $this->assertNotNull($row);
        $this->assertEquals('NewName', $row['name']);
    }
}
