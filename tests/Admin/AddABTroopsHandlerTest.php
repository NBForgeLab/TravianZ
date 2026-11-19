<?php

namespace Tests\Admin;

use PHPUnit\Framework\TestCase;
use App\Admin\Services\AddABTroopsHandler;

class AddABTroopsHandlerTest extends TestCase
{
    public function testHandleUpdatesABTroops()
    {
        $db = $this->createMock(\MYSQLi_DB::class);
        $db->method('getVillage')->willReturn(['name' => 'TestVillage']);
        $db->method('escape')->willReturnArgument(0);
        
        $queries = [];
        $db->expects($this->exactly(2))
           ->method('query')
           ->willReturnCallback(function (string $sql) use (&$queries) {
               $queries[] = $sql;
               return true;
           });
        
        $handler = new AddABTroopsHandler($db);
        $postData = [
            'id' => 1,
            'a1' => 5,
            'b1' => 7
        ];
        
        $result = $handler->handle($postData, 123);
        $this->assertTrue($result);
        $this->assertStringContainsString('UPDATE ' . TB_PREFIX . 'abdata SET', $queries[0]);
        $this->assertStringContainsString('a1 = 5', $queries[0]);
        $this->assertStringContainsString('b1 = 7', $queries[0]);
        $this->assertStringContainsString('INSERT INTO ' . TB_PREFIX . 'admin_log', $queries[1]);
    }
}
