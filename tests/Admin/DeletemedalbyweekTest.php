<?php
namespace Tests\Admin;

use App\Admin\Services\DeletemedalbyweekService;
use App\Database\IDbConnection;
use PHPUnit\Framework\TestCase;

class DeletemedalbyweekTest extends TestCase
{
    public function testDeleteMedalsByWeek()
    {
        $dbMock = $this->createMock(IDbConnection::class);
        $dbMock->expects($this->once())
            ->method('query')
            ->with($this->stringContains('UPDATE prefix_medal SET del = 1 WHERE week = 5 AND del = 0'));
        $dbMock->expects($this->once())
            ->method('affected_rows')
            ->willReturn(5);

        $service = new DeletemedalbyweekService($dbMock, 'prefix_');
        $this->assertEquals(5, $service->deleteMedalsByWeek(5));
    }
}
