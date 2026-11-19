<?php
namespace Tests\Admin;

use PHPUnit\Framework\TestCase;
use App\Admin\Services\GivePlusHandler;

class GivePlusHandlerTest extends TestCase
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
        
        $handler = new GivePlusHandler($db);
        $result = $handler->handle(7, 99);
        
        $this->assertTrue($result);
        $this->assertStringContainsString('UPDATE ' . TB_PREFIX . 'users', $queries[0]);
        $this->assertStringContainsString((string)(7 * 86400), $queries[0]);
        $this->assertStringContainsString('INSERT INTO ' . TB_PREFIX . 'admin_log', $queries[1]);
    }
}
