<?php
namespace Tests\Admin;

require_once __DIR__ . '/../DatabaseTestCase.php';

use App\Admin\Services\DeleteUserHandler;
use Tests\DatabaseTestCase;

class DeleteUserHandlerTest extends DatabaseTestCase
{
    public function testHandleDeletesUserAndLogsAction()
    {
        $db = $GLOBALS['database'];
        $uid = 9876;

        $db->query("INSERT INTO " . TB_PREFIX . "users (id, username, email) VALUES ($uid, 'deltarget', 'deltarget@example.com')");
        $db->query("INSERT INTO " . TB_PREFIX . "hero (uid, name) VALUES ($uid, 'HeroDel')");

        $handler = new DeleteUserHandler($db);
        $result = $handler->handle($uid, 1);

        $this->assertTrue($result);
        $check = $db->query("SELECT id FROM " . TB_PREFIX . "users WHERE id = $uid");
        $this->assertEquals(0, $db->num_rows($check));

        $logRes = $db->query("SELECT log FROM " . TB_PREFIX . "admin_log WHERE `user` = '1' ORDER BY id DESC LIMIT 1");
        $logRow = mysqli_fetch_assoc($logRes);
        $this->assertNotNull($logRow);
        $this->assertStringContainsString("Deleted player ID $uid", $logRow['log']);
    }

    public function testHandleReturnsFalseForMissingUser()
    {
        $db = $this->getMockBuilder(\MYSQLi_DB::class)->disableOriginalConstructor()->getMock();
        $db->method('getUserArray')->willReturn(false);

        $handler = new DeleteUserHandler($db);
        $this->assertFalse($handler->handle(999999, 1));
    }
}
