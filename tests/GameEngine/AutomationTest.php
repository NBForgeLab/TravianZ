<?php
namespace Tests\GameEngine;

require_once __DIR__ . '/../DatabaseTestCase.php';

use Tests\DatabaseTestCase;
use Automation;

class AutomationTest extends DatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['database']->query("DELETE FROM " . TB_PREFIX . "deleting");
    }

    public function testAutomationRuns()
    {
        $automation = new Automation($GLOBALS['database']);
        $this->assertNotNull($automation);
        $automation->run();
        $this->assertTrue(true);
    }
}
