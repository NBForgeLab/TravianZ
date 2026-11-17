<?php
namespace Tests\GameEngine;
require_once __DIR__ . '/../bootstrap_lite.php';
require_once __DIR__ . '/../../GameEngine/Message.php';

use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase {
    private $dbMock;

    protected function setUp(): void {
        $this->dbMock = new class {
            public function getUnreadMessagesCount($uid) { return 0; }
            public function getUnreadNoticesCount($uid) { return 0; }
            public function getMessage($uid, $mode) { return []; }
            public function getMessageOwnership($id) { return ['target' => 1, 'owner' => 1]; }
            public function __call($name, $args) { return null; }
        };
        $GLOBALS['database'] = $this->dbMock;
        $GLOBALS['session'] = new class {
            public $uid = 1;
            public $alliance = 0;
            public $plus = false;
            public $access = 0;
        };
    }

    public function testMessageCanBeInstantiated() {
        $message = new \Message();
        $this->assertInstanceOf(\Message::class, $message);
    }

    public function testLoadMessage() {
        $message = new \Message();
        $message->loadMessage(1);
        $this->assertEquals([], $message->reading);
    }

    public function testGetReportType() {
        $message = new \Message();
        $this->assertEquals(1, $message->getReportType(2));
        $this->assertEquals(10, $message->getReportType(11));
        $this->assertEquals(99, $message->getReportType(99));
    }
}
