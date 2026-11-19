<?php
namespace Tests\GameEngine\Database;

require_once __DIR__ . '/../../DatabaseTestCase.php';
require_once __DIR__ . '/../../../GameEngine/Database.php';

use Tests\DatabaseTestCase;

class UserQueriesTest extends DatabaseTestCase
{
    private $db;
    private $savedSession;

    protected function setUp(): void
    {
        parent::setUp();
        $this->savedSession = $GLOBALS['session'] ?? null;
        $this->db = $GLOBALS['database'];

        // Mock global session to avoid warnings
        $GLOBALS['session'] = new class {
            public $uid = 0;
            public $username = 'Tester';
        };
    }

    protected function tearDown(): void
    {
        $GLOBALS['session'] = $this->savedSession;
        parent::tearDown();
    }

    public function testRegisterAndGetUserField()
    {
        // Setup dummy data
        $uid = $this->db->register('TestUser', 'password', 'test@example.com', 1, 'act123');
        $this->assertIsInt($uid);
        $this->assertGreaterThan(0, $uid);
        
        $userArray = $this->db->getUserArray($uid, 1, false);
        $username = $userArray['username'] ?? 'NOT_FOUND';
        $this->assertEquals('TestUser', $username);
    }

    public function testCheckFriendsDoesNotRelyOnGlobals()
    {
        // Setup dummy user with a sparse friend slot
        $uid = $this->db->register('FriendTest', 'password', 'test@example.com', 1, 'act123');
        $this->db->query("UPDATE " . TB_PREFIX . "users SET friend0 = 0, friend1 = 42 WHERE id = $uid");

        // Ensure globals are not required cleanly
        $GLOBALS['session'] = null;

        $this->db->checkFriends($uid);

        $this->assertEquals(42, (int)$this->db->getUserField($uid, 'friend0', 0, false));
    }
}
