<?php
namespace Tests\GameEngine;
require_once __DIR__ . '/../DatabaseTestCase.php';
require_once __DIR__ . '/../../GameEngine/Message.php';

class MessageCoverageTest extends \Tests\DatabaseTestCase
{
    private $message;

    protected function setUp(): void
    {
        parent::setUp();
        // Mock session and database if possible, or use global
        $this->message = new \Message();
    }

    public function testGetReportType()
    {
        $this->assertEquals(1, $this->message->getReportType(2));
        $this->assertEquals(10, $this->message->getReportType(11));
        $this->assertEquals(15, $this->message->getReportType(16));
        $this->assertEquals(3, $this->message->getReportType(19));
        $this->assertEquals(22, $this->message->getReportType(23));
        $this->assertEquals(24, $this->message->getReportType(24));
        $this->assertEquals(26, $this->message->getReportType(26));
        $this->assertEquals(27, $this->message->getReportType(27));
        $this->assertEquals(99, $this->message->getReportType(99));
    }
}
