<?php
namespace Tests;

require_once __DIR__ . '/../DatabaseTestCase.php';
require_once __DIR__ . '/../../GameEngine/Technology.php';

class TechnologyTest extends DatabaseTestCase
{
    private $db;
    private $tech;

    protected function setUp(): void
    {
        parent::setUp();
        if (!defined('U1')) define('U1', 'Unit1');
        if (!defined('SPEED')) define('SPEED', 1);
        
        $this->db = $GLOBALS['database'];
        $village = (object) ['wid' => 1, 'researching' => [['tech' => 'a1'], ['tech' => 't1']], 'awood' => 100, 'aclay' => 100, 'airon' => 100, 'acrop' => 100];
        $this->tech = new \Technology($this->db, $village);
    }

    public function testGetABUpgrades()
    {
        $result = $this->tech->getABUpgrades('a');
        $this->assertCount(1, $result);
        $this->assertEquals('a1', $result[0]['tech']);
    }

    public function testIsResearch()
    {
        $village = (object) ['wid' => 1, 'researching' => [['tech' => 't1'], ['tech' => 't2']]];
        $this->tech = new \Technology($this->db, $village);
        
        $this->assertTrue($this->tech->isResearch(1, 1));
        $this->assertFalse($this->tech->isResearch(3, 1));
    }
}
