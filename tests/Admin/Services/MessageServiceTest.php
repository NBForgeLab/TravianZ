<?php
namespace Tests\Admin\Services;

use App\Admin\Services\MessageService;
use App\Database\IDbConnection;
use PHPUnit\Framework\TestCase;

class MessageServiceTest extends TestCase
{
    private $messageService;
    private $dbMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbMock = $this->createMock(IDbConnection::class);
        $this->messageService = new MessageService($this->dbMock);
    }

    public function testSendMessage()
    {
        $queries = [];
        $this->dbMock->expects($this->exactly(2))
            ->method('query')
            ->willReturnCallback(function (string $sql) use (&$queries) {
                $queries[] = $sql;
                return true;
            });

        $this->dbMock->method('escape')->willReturnArgument(0);

        $this->messageService->sendMessage(20, 10, 'Test Topic', 'Test Message');

        $this->assertStringContainsString('INSERT INTO ' . TB_PREFIX . 'mdata', $queries[0]);
        $this->assertStringContainsString('Test Topic', $queries[0]);
        $this->assertStringContainsString('INSERT INTO ' . TB_PREFIX . 'admin_log', $queries[1]);
    }
}
