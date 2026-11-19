<?php
namespace Tests\Admin;

use App\Admin\Services\EditPlusService;
use App\Database\IDbConnection;
use PHPUnit\Framework\TestCase;

class EditPlusTest extends TestCase
{
    public function testUpdatePlusBonuses()
    {
        $dbMock = $this->createMock(IDbConnection::class);
        
        $dbMock->expects($this->once())
            ->method('updateUserField')
            ->with(1, ['plus', 'b1'], [86400, 172800], 1);

        $service = new EditPlusService($dbMock, 'prefix_');
        $service->updatePlusBonuses(1, ['plus' => 86400, 'b1' => 172800]);
    }
    
    public function testLogPlusChange()
    {
        $dbMock = $this->createMock(IDbConnection::class);
        
        $dbMock->expects($this->once())
            ->method('query_new')
            ->with($this->stringContains('INSERT INTO prefix_admin_log'));

        $service = new EditPlusService($dbMock, 'prefix_');
        $service->logPlusChange(1, 2, 'logText');
    }
}
