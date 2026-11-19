<?php
namespace Tests\Admin\Services;

use PHPUnit\Framework\TestCase;
use App\Admin\Services\SitterEditorService;

class SitterEditorServiceTest extends TestCase
{
    public function testUpdateSitters()
    {
        $db = $this->createMock(\MYSQLi_DB::class);
        $db->expects($this->once())
           ->method('query')
           ->with($this->logicalAnd(
               $this->stringContains('UPDATE ' . TB_PREFIX . 'users'),
               $this->stringContains('sit1 = 2'),
               $this->stringContains('sit2 = 3'),
               $this->stringContains('WHERE id = 1')
           ));

        $service = new SitterEditorService($db);
        $service->updateSitters(1, 2, 3);
    }
}
