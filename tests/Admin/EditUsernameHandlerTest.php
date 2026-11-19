<?php

namespace Tests\Admin;

use PHPUnit\Framework\TestCase;
use App\Admin\Services\EditUsernameHandler;

class EditUsernameHandlerTest extends TestCase
{
    public function testHandleSuccessAndValidationPaths()
    {
        $db = $this->createMock(\MYSQLi_DB::class);
        
        $mockResult = new class {
            public $num_rows = 0;
        };
        
        $queries = [];
        $db->method('query')->willReturnCallback(function (string $sql) use (&$queries, $mockResult) {
            $queries[] = $sql;
            return $mockResult;
        });
        $db->method('escape')->willReturnArgument(0);
        
        $handler = new EditUsernameHandler($db);
        $result = $handler->handle(1, 'newname', 99);
        
        $this->assertEquals(['status' => 'success'], $result);
        $this->assertStringContainsString("UPDATE " . TB_PREFIX . "users SET username = 'newname' WHERE id = 1", $queries[1]);
        $this->assertStringContainsString("Changed username for user 1 to 'newname'", $queries[2]);

        $this->assertEquals(['status' => 'invalid'], $handler->handle(1, 'a', 99));

        $mockResult->num_rows = 1;
        $this->assertEquals(['status' => 'taken'], $handler->handle(1, 'newname', 99));
    }
}
