<?php
namespace Tests\Admin;

use App\Admin\Services\EditPasswordService;
use App\Database\IDbConnection;
use PHPUnit\Framework\TestCase;

class EditPasswordTest extends TestCase
{
    public function testUpdatePassword()
    {
        $dbMock = $this->createMock(IDbConnection::class);
        
        $dbMock->expects($this->once())
            ->method('query_new')
            ->with($this->stringContains('UPDATE prefix_users SET password = ? WHERE id = ?'));

        $service = new EditPasswordService($dbMock, 'prefix_');
        $service->updatePassword(1, 'newPassword');
    }
    
    public function testLogPasswordChange()
    {
        $dbMock = $this->createMock(IDbConnection::class);
        
        $dbMock->expects($this->once())
            ->method('query_new')
            ->with($this->stringContains('INSERT INTO prefix_admin_log'));

        $service = new EditPasswordService($dbMock, 'prefix_');
        $service->logPasswordChange(1, 2, 'targetUser');
    }
}
