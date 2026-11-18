<?php
namespace Tests\GameEngine\Database;

require_once __DIR__ . '/../../DatabaseTestCase.php';
require_once __DIR__ . '/../../../GameEngine/Database.php';

use Tests\DatabaseTestCase;

class UserQueriesTest extends DatabaseTestCase
{
    private $db;

    protected function setUp(): void
    {
        parent::setUp();
        // Assuming $GLOBALS['database'] is set by DatabaseTestCase
        $this->db = $GLOBALS['database'];
        
        // Mock global session to avoid warnings
        $GLOBALS['session'] = new class {
            public $uid = 0;
            public $username = 'Tester';
        };
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
        // Setup dummy user
        $uid = $this->db->register('FriendTest', 'password', 'test@example.com', 1, 'act123');
        
        // Mock global session explicitly for this test if needed, but it should not be accessed
        $GLOBALS['session'] = null; // Ensure it's not set
        
        // This should run without accessing $GLOBALS['session']
        $this->db->checkFriends($uid);
        
        $this->assertTrue(true);
    }
}
