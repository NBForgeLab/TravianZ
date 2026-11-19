<?php
namespace Tests\Admin;

use PHPUnit\Framework\TestCase;
use App\Admin\Services\EditWeekHandler;

class EditWeekHandlerTest extends TestCase
{
    public function testHandleSuccess()
    {
        $db = $this->createMock(\MYSQLi_DB::class);
        $db->method('escape')->willReturnArgument(0);
        
        $queries = [];
        $db->expects($this->exactly(2))
           ->method('query')
           ->willReturnCallback(function (string $sql) use (&$queries) {
               $queries[] = $sql;
               return true;
           });
        
        $handler = new EditWeekHandler($db);
        $result = $handler->handle(1, 100, 200, 300, 99);
        
        $this->assertTrue($result);
        $this->assertStringContainsString('UPDATE ' . TB_PREFIX . 'users SET ap = 100, dp = 200, RR = 300 WHERE id = 1', $queries[0]);
        $this->assertStringContainsString('INSERT INTO ' . TB_PREFIX . 'admin_log', $queries[1]);
    }
}
