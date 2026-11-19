<?php
namespace Tests\GameEngine\Admin\Handler;

use App\Admin\Services\GivePlusResHandler;
use PHPUnit\Framework\TestCase;

class GivePlusResHandlerTest extends TestCase
{
    private $mockDatabase;
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockDatabase = $this->getMockBuilder(\MYSQLi_DB::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['query', 'escape'])
            ->getMock();

        $this->handler = new GivePlusResHandler($this->mockDatabase);
    }

    public function testGivePlusResUpdatesDatabaseAndLogs()
    {
        $postData = ['wood' => 1, 'clay' => 1, 'iron' => 1, 'crop' => 1];
        $adminId = 1;
        $time = time();

        $this->mockDatabase->expects($this->exactly(5))
            ->method('query');
        $this->mockDatabase->method('escape')
            ->willReturnArgument(0);

        $result = $this->handler->givePlusRes($postData, $adminId, $time);

        $this->assertTrue($result);
    }

    public function testGivePlusResReturnsFalseWhenAllZero()
    {
        $this->mockDatabase->expects($this->never())->method('query');
        $this->assertFalse($this->handler->givePlusRes([], 1, time()));
    }
}
