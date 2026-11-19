<?php
namespace Tests\Admin;

use App\Admin\Services\EditAccessService;
use App\Database\IDbConnection;
use PHPUnit\Framework\TestCase;

class EditAccessTest extends TestCase
{
    public function testUpdateUserAccess()
    {
        $dbMock = $this->createMock(IDbConnection::class);
        $dbMock->expects($this->once())
            ->method('query')
            ->with($this->stringContains('UPDATE prefix_users SET access = 9 WHERE id = 1'));

        $service = new EditAccessService($dbMock, 'prefix_');
        $service->updateUserAccess(1, 9);
    }
}
