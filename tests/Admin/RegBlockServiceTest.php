<?php

namespace Tests\Admin;

use App\Admin\Services\RegBlockService;
use Tests\DatabaseTestCase;

class RegBlockServiceTest extends DatabaseTestCase
{
    public function testAddAndRemoveBlock()
    {
        $database = $GLOBALS['database'];
        $service = new RegBlockService($database);
        
        list($ok, $msg) = $service->add('username', 'testuser', 'blocked', 1);
        $this->assertTrue($ok);
        $this->assertEquals('Block saved.', $msg);
        
        // Verify it exists in db
        $res = $database->query("SELECT id FROM " . TB_PREFIX . "reg_block WHERE value = 'testuser'");
        $row = mysqli_fetch_assoc($res);
        $this->assertNotEmpty($row);
        
        $id = $row['id'];
        $ok = $service->remove($id);
        $this->assertTrue($ok);
        
        // Verify it's gone
        $res = $database->query("SELECT id FROM " . TB_PREFIX . "reg_block WHERE id = $id");
        $this->assertEquals(0, mysqli_num_rows($res));
    }
}
