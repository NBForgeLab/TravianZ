<?php
namespace Tests\Entity;

require_once __DIR__ . '/../DatabaseTestCase.php';

use App\Entity\User;
use Tests\DatabaseTestCase;

class UserTest extends DatabaseTestCase {
    public function testConstructorWithId() {
        $user = new User(1);
        $this->assertEquals(1, $user->getId());
    }

    public function testConstructorWithUsername() {
        $user = new User("testuser");
        $this->assertEquals("testuser", $user->getUsername());
    }

    public function testExists() {
        $db = $GLOBALS['database'];
        
        // Setup: insert a user
        $conn = $GLOBALS['link'];
        mysqli_query($conn, "INSERT INTO " . TB_PREFIX . "users (username, email) VALUES ('testuser', 'test@example.com')");
        
        // Test
        $this->assertTrue(User::exists($db, "testuser"));
        $this->assertTrue(User::exists($db, "test@example.com"));
        $this->assertFalse(User::exists($db, "nonexistent"));
    }
}
