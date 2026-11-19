<?php
namespace Tests\Admin\Services;

use App\Admin\Services\PushProtectionService;
use App\Database\IDbConnection;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../GameEngine/PushProtection.php';

class PushProtectionServiceTest extends TestCase
{
    private $ppService;
    private $dbMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dbMock = $this->createMock(IDbConnection::class);
        $this->ppService = new PushProtectionService($this->dbMock);
    }

    public function testSetOverride()
    {
        $uid = 20;
        $mode = \PushProtection::OV_CUSTOM;
        $customLimit = 5000;
        $note = 'Test Note';
        $adminId = 10;

        $this->dbMock->expects($this->once())
            ->method('query')
            ->with($this->logicalAnd(
                $this->stringContains('INSERT INTO ' . TB_PREFIX . 'admin_log'),
                $this->stringContains('custom cap 5000')
            ));

        $this->dbMock->expects($this->once())
            ->method('escape')
            ->willReturnArgument(0);

        $this->ppService->setOverride($uid, $mode, $customLimit, $note, $adminId);
    }
}
