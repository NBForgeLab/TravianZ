<?php
namespace Tests\GameEngine\Admin;

require_once __DIR__ . '/../../DatabaseTestCase.php';
require_once __DIR__ . '/../../../GameEngine/Admin/database.php';

class DatabaseAdminTest extends \Tests\DatabaseTestCase
{
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // Use the injected instance
        $this->admin = new \adm_DB($GLOBALS['database']);
    }

    public function testIsConnected()
    {
        $this->assertNotNull($this->admin->connection);
    }
}
