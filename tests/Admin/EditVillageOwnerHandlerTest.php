<?php
namespace Tests\Admin;

use PHPUnit\Framework\TestCase;
use App\Admin\Services\EditVillageOwnerHandler;

class EditVillageOwnerHandlerTest extends TestCase
{
    public function testHandleSuccessAndErrorPaths()
    {
        $db = $this->createMock(\MYSQLi_DB::class);
        
        $db->method('getVillage')->willReturnCallback(fn($did) => $did === 10 ? ['owner' => 1] : []);
        $db->method('getUserArray')->willReturnCallback(fn($uid) => $uid === 2 ? ['id' => 2] : []);
        $db->method('escape')->willReturnArgument(0);
        
        $queries = [];
        $db->method('query')->willReturnCallback(function (string $sql) use (&$queries) {
            $queries[] = $sql;
            return true;
        });
        
        $handler = new EditVillageOwnerHandler($db);
        $result = $handler->handle(10, 2, 99);
        
        $this->assertEquals(['status' => 'success', 'newowner' => 2], $result);
        $this->assertStringContainsString('UPDATE ' . TB_PREFIX . 'vdata SET owner = 2 WHERE wref = 10', $queries[0]);

        $this->assertEquals(['status' => 'novillage'], $handler->handle(999, 2, 99));
        $this->assertEquals(['status' => 'nouser'], $handler->handle(10, 999, 99));
    }
}
