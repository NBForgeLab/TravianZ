<?php
namespace Tests\Admin;

use App\Admin\Services\EditProtectionService;
use App\Database\IDbConnection;
use PHPUnit\Framework\TestCase;

class EditProtectionTest extends TestCase
{
    public function testUpdateProtection()
    {
        $dbMock = $this->createMock(IDbConnection::class);
        
        $dbMock->expects($this->once())
            ->method('query_new')
            ->with($this->stringContains('UPDATE prefix_users SET protect = ? WHERE id = ?'));

        $service = new EditProtectionService($dbMock, 'prefix_');
        $service->updateProtection(1, 7);
    }
    
    public function testLogProtectionChange()
    {
        $dbMock = $this->createMock(IDbConnection::class);
        
        $dbMock->expects($this->once())
            ->method('query_new')
            ->with($this->stringContains('INSERT INTO prefix_admin_log'));

        $service = new EditProtectionService($dbMock, 'prefix_');
        $service->logProtectionChange(1, 2, 'targetUser', 7);
    }
}
