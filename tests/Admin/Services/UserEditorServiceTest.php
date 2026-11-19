<?php
namespace Tests\Admin\Services;

use PHPUnit\Framework\TestCase;
use App\Admin\Services\UserEditorService;

class UserEditorServiceTest extends TestCase
{
    public function testUpdateProfile()
    {
        $db = $this->createMock(\MYSQLi_DB::class);
        $db->method('escape')->willReturnArgument(0);
        
        $db->expects($this->once())
           ->method('query')
           ->with($this->logicalAnd(
               $this->stringContains('UPDATE ' . TB_PREFIX . 'users SET'),
               $this->stringContains("email = 'test@test.com'"),
               $this->stringContains('tribe = 1'),
               $this->stringContains("location = 'test location'"),
               $this->stringContains("desc1 = 'desc1'"),
               $this->stringContains("desc2 = 'desc2'"),
               $this->stringContains("quest = 'quest'"),
               $this->stringContains('WHERE id = 1')
           ));

        $service = new UserEditorService($db);
        $service->updateProfile(1, [
            'email' => 'test@test.com',
            'tribe' => 1,
            'location' => 'test location',
            'desc1' => 'desc1',
            'desc2' => 'desc2',
            'quest' => 'quest'
        ]);
    }
}
