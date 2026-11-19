<?php
namespace Tests\Admin\Services;

use PHPUnit\Framework\TestCase;
use App\Admin\Services\ResourceEditorService;

class ResourceEditorServiceTest extends TestCase
{
    public function testUpdateResources()
    {
        $db = $this->createMock(\MYSQLi_DB::class);
        $db->expects($this->once())
           ->method('query')
           ->with($this->logicalAnd(
               $this->stringContains('UPDATE ' . TB_PREFIX . 'vdata SET'),
               $this->stringContains('wood = 100'),
               $this->stringContains('clay = 50'),
               $this->stringContains('iron = 20'),
               $this->stringContains('crop = 10'),
               $this->stringContains('maxstore = 1000'),
               $this->stringContains('maxcrop = 1000'),
               $this->stringContains('WHERE wref = 1')
           ));

        $service = new ResourceEditorService($db);
        $service->updateResources(1, [
            'wood' => 100,
            'clay' => 50,
            'iron' => 20,
            'crop' => 10,
            'maxstore' => 1000,
            'maxcrop' => 1000
        ]);
    }
}
