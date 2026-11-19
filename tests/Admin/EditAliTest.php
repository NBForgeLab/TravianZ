<?php
namespace Tests\Admin;

use App\Admin\Services\EditAliService;
use App\Database\IDbConnection;
use PHPUnit\Framework\TestCase;

class EditAliTest extends TestCase
{
    public function testUpdateAlliance()
    {
        $dbMock = $this->createMock(IDbConnection::class);
        $dbMock->method('escape')->willReturnArgument(0);
        $dbMock->expects($this->once())
            ->method('query')
            ->with($this->logicalAnd(
                $this->stringContains('UPDATE prefix_alidata SET'),
                $this->stringContains("tag = 'Tag'"),
                $this->stringContains("name = 'Name'"),
                $this->stringContains('`max` = 3')
            ));

        $service = new EditAliService($dbMock, 'prefix_');
        $service->updateAlliance(1, ['tag' => 'Tag', 'name' => 'Name']);
    }
}
