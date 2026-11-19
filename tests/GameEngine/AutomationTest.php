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
        $db = $GLOBALS['database'];
        $uid = 8810;
        $db->query("DELETE FROM " . TB_PREFIX . "banlist WHERE uid = $uid");
        $db->query("DELETE FROM " . TB_PREFIX . "users WHERE id = $uid");
        $db->query("INSERT INTO " . TB_PREFIX . "users (id, username, email, access) VALUES ($uid, 'bantarget', 'ban@example.com', 0)");
        $db->query("INSERT INTO " . TB_PREFIX . "banlist (uid, name, reason, time, `end`, admin, active) VALUES ($uid, 'bantarget', 'test', 100, 200, 1, 1)");

        $automation = new Automation($db);
        $this->assertNotNull($automation);
        $automation->run();

        $res = mysqli_query($GLOBALS['link'], "SELECT active FROM " . TB_PREFIX . "banlist WHERE uid = $uid LIMIT 1");
        $banRow = mysqli_fetch_assoc($res);
        $this->assertEquals(0, (int)$banRow['active']);

        $userRes = mysqli_query($GLOBALS['link'], "SELECT access FROM " . TB_PREFIX . "users WHERE id = $uid LIMIT 1");
        $userRow = mysqli_fetch_assoc($userRes);
        $this->assertEquals(2, (int)$userRow['access']);
    }
}
