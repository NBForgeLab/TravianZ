<?php

namespace Tests\Admin;

use App\Admin\Services\AdditionalService;
use Tests\DatabaseTestCase;

class AdditionalServiceTest extends DatabaseTestCase
{
    public function testUpdateAdditional()
    {
        // Use the global database connection initialized by bootstrap
        $database = $GLOBALS['database'];
        
        $service = new AdditionalService($database);
        
        // Setup dummy user cleanly inside transaction
        $database->query("DELETE FROM " . TB_PREFIX . "users WHERE id = 999");
        $database->query("INSERT INTO " . TB_PREFIX . "users (id, username, gold) VALUES (999, 'TestPlayer', 100)");
        
        $service->updateAdditional(
            999, 2, 200, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
            1, 'Admin', 'TestPlayer'
        );
        
        $newGold = (int)$database->getUserField(999, 'gold', 0, false);
        $this->assertEquals(200, $newGold);
        
        // Verify logs
        $log = $database->query("SELECT * FROM " . TB_PREFIX . "admin_log WHERE `user` = '1' ORDER BY time DESC LIMIT 1");
        $logRow = mysqli_fetch_assoc($log);
        $this->assertStringContainsString('Gold: 100 → 200', $logRow['log']);
    }
}
