<?php
namespace Tests\Admin;

use App\Admin\Services\EditAdminInfoService;
use App\Database\IDbConnection;
use PHPUnit\Framework\TestCase;

class EditAdminInfoTest extends TestCase
{
    public function testLogAdminAction()
    {
        $dbMock = $this->createMock(IDbConnection::class);
        $dbMock->method('escape')->willReturnArgument(0);
        $dbMock->expects($this->once())
            ->method('query')
            ->with($this->logicalAnd(
                $this->stringContains('INSERT INTO prefix_admin_log'),
                $this->stringContains('Log message')
            ));

        $service = new EditAdminInfoService($dbMock, 'prefix_');
        $service->logAdminAction(1, 'Log message');
    }
}
