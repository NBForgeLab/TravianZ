<?php

namespace Tests\Admin;

use App\Admin\Services\EditBuildingsHandler;
use PHPUnit\Framework\TestCase;

class EditBuildingsHandlerTest extends TestCase
{
    public function testHandleUpdatesBuildingsAndLogs()
    {
        $db = $this->getMockBuilder(\MYSQLi_DB::class)
            ->disableOriginalConstructor()
            ->getMock();

        $db->method('getResourceLevel')->willReturn(['f99t' => 1, 'f99' => 1]);
        $db->method('getVillage')->willReturn(['name' => 'TestVillage']);
        $db->method('escape')->willReturnArgument(0);
        $db->expects($this->exactly(2))->method('query');

        $automation = new class {
            public function recountPop($id) { return 100; }
            public function buildingPOP($gid, $lvl) { return 0; }
        };

        $handler = new EditBuildingsHandler($db, $automation);
        $result = $handler->handle([
            'id' => 1,
            'id1level' => 5,
            'id1gid' => 1,
        ], 123);

        $this->assertTrue($result);
    }

    public function testHandleReturnsFalseForInvalidVillageId()
    {
        $db = $this->getMockBuilder(\MYSQLi_DB::class)
            ->disableOriginalConstructor()
            ->getMock();
        $db->expects($this->never())->method('query');

        $automation = new class {
            public function recountPop($id) { return 0; }
            public function buildingPOP($gid, $lvl) { return 0; }
        };

        $handler = new EditBuildingsHandler($db, $automation);
        $this->assertFalse($handler->handle(['id' => 0], 123));
    }
}
