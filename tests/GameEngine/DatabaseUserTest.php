<?php
namespace Tests\GameEngine;

require_once __DIR__ . '/../DatabaseTestCase.php';

class DatabaseUserTest extends \Tests\DatabaseTestCase
{
    private $database;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = $GLOBALS['database'];
    }

    public function testRegisterAndGetUser()
    {
        $uid = $this->database->register("TestUser", "password", "test@example.com", 1, "act");
        $this->assertGreaterThan(0, $uid);
        
        $user = $this->database->getUserArray($uid, 1);
        $this->assertEquals("TestUser", $user['username']);
        $this->assertEquals("test@example.com", $user['email']);
    }

    public function testUpdateUserField()
    {
        $uid = $this->database->register("UpdateUser", "password", "update@example.com", 1, "act");
        $this->database->updateUserField($uid, "email", "newemail@example.com", 1);
        
        $user = $this->database->getUserArray($uid, 1);
        $this->assertEquals("newemail@example.com", $user['email']);
    }
}
