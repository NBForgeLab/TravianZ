<?php

namespace Tests\Admin;

use PHPUnit\Framework\TestCase;
use App\Admin\Services\AddTroopsHandler;

class AddTroopsHandlerTest extends TestCase
{
    public function testHandleUpdatesUnits()
    {
        $db = $this->createMock(\MYSQLi_DB::class);
        
        $db->method('getVillage')->willReturn(['owner' => 1, 'name' => 'TestVillage']);
        $db->method('getUserArray')->willReturn(['tribe' => 1]);
        $db->method('escape')->willReturnArgument(0);
        
        $queries = [];
        $db->expects($this->exactly(2))
           ->method('query')
           ->willReturnCallback(function (string $sql) use (&$queries) {
               $queries[] = $sql;
               return true;
           });
        $db->expects($this->once())->method('addStarvationData')->with(1);
        
        $handler = new AddTroopsHandler($db);
        $postData = [
            'id' => 1,
            'u1' => 10,
            'u2' => 20
        ];
        
        $result = $handler->handle($postData, 123);
        $this->assertTrue($result);
        $this->assertStringContainsString('UPDATE ' . TB_PREFIX . 'units SET u1 = 10, u2 = 20', $queries[0]);
        $this->assertStringContainsString('WHERE vref = 1', $queries[0]);
        $this->assertStringContainsString('INSERT INTO ' . TB_PREFIX . 'admin_log', $queries[1]);
    }

    public function testHandleReturnsFalseWhenVillageDoesNotExist()
    {
        $db = $this->createMock(\MYSQLi_DB::class);
        $db->method('getVillage')->willReturn([]);
        $db->expects($this->never())->method('query');

        $handler = new AddTroopsHandler($db);
        $this->assertFalse($handler->handle(['id' => 99999, 'u1' => 10], 123));
    }
}
