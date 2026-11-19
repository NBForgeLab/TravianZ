<?php
namespace Tests\Repository;

require_once __DIR__ . '/../DatabaseTestCase.php';

use App\Repository\UserRepository;
use Tests\DatabaseTestCase;

class UserRepositoryTest extends DatabaseTestCase {
    public function testExists() {
        // Use the global database connection
        $db = $GLOBALS['database'];
        $repo = new UserRepository($db);

        // Setup: insert a user
        // Using raw query to insert, avoiding query_new for setup to be safe
        $conn = $GLOBALS['link'];
        mysqli_query($conn, "INSERT INTO " . TB_PREFIX . "users (username, email) VALUES ('testuser', 'test@example.com')");
        
        $check = $db->query_new("SELECT * FROM " . TB_PREFIX . "users WHERE username = ?", "testuser");
        $this->assertNotEmpty($check, "User was not inserted or SELECT failed. Table: " . TB_PREFIX . "users");

        // Test
        $res = $repo->exists("testuser");
        $this->assertTrue($res, "Failed for testuser");
        
        $res2 = $repo->exists("test@example.com");
        $this->assertTrue($res2, "Failed for test@example.com");
        
        $res3 = $repo->exists("nonexistent");
        $this->assertFalse($res3, "Failed for nonexistent");
    }
}
