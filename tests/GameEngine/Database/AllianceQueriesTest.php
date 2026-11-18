<?php
namespace Tests\GameEngine\Database;

require_once __DIR__ . '/../../DatabaseTestCase.php';
require_once __DIR__ . '/../../../GameEngine/Database.php';

use Tests\DatabaseTestCase;

class AllianceQueriesTest extends DatabaseTestCase
{
    private $db;

    protected function setUp(): void
    {
        parent::setUp();
        // Assuming $GLOBALS['database'] is set by DatabaseTestCase
        $this->db = $GLOBALS['database'];
    }

    public function testGetAllianceName()
    {
        // Setup dummy data
        $this->db->query("INSERT INTO " . TB_PREFIX . "alidata (id, name, tag) VALUES (1, 'Alliance', 'ALL')");
        
        $name = $this->db->getAllianceName(1);
        $this->assertEquals('ALL', $name);
    }
}
