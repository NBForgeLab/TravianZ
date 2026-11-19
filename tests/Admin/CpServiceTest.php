<?php

namespace Tests\Admin;

use App\Admin\Services\CpService;
use Tests\DatabaseTestCase;

class CpServiceTest extends DatabaseTestCase
{
    public function testUpdateCp()
    {
        $database = $GLOBALS['database'];
        $service = new CpService($database);
        
        // Setup dummy user
        $database->query("INSERT INTO " . TB_PREFIX . "users (id, cp) VALUES (998, 100)");
        
        $service->updateCp(998, 50);
        
        $newCp = (int)$database->getUserField(998, 'cp', 0, false);
        $this->assertEquals(150, $newCp);
    }
}
