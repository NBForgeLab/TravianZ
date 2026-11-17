<?php
namespace Tests\GameEngine;
require_once __DIR__ . '/../DatabaseTestCase.php';

class DatabaseMessageQueriesTest extends \Tests\DatabaseTestCase
{
    private $database;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = $GLOBALS['database'];
    }

    public function testAddAndGetNotice()
    {
        $uid = 1;
        $toWref = 10;
        $ally = 0;
        $type = 1;
        $topic = "Test Report";
        $data = "Test Data";

        $this->database->addNotice($uid, $toWref, $ally, $type, $topic, $data);
        
        $notices = $this->database->getNotice($uid);
        $this->assertCount(1, $notices);
        $this->assertEquals($topic, $notices[0]['topic']);
        $this->assertEquals($data, $notices[0]['data']);
    }

    public function testNoticeViewedAll()
    {
        $uid = 1;
        $this->database->addNotice($uid, 10, 0, 1, "Report 1", "Data 1");
        $this->database->addNotice($uid, 11, 0, 1, "Report 2", "Data 2");
        
        $this->database->noticeViewedAll($uid);
        
        $notices = $this->database->getUnViewNotice($uid);
        $this->assertCount(0, $notices);
    }

    public function testRemoveNotice()
    {
        $uid = 1;
        $this->database->addNotice($uid, 10, 0, 1, "Report 1", "Data 1");
        $notices = $this->database->getNotice($uid);
        $id = $notices[0]['id'];
        
        $this->database->removeNotice([$id], $uid);
        
        $notices = $this->database->getNotice($uid);
        $this->assertCount(0, $notices);
    }

    public function testArchiveNotice()
    {
        $uid = 1;
        $this->database->addNotice($uid, 10, 0, 1, "Report 1", "Data 1");
        $notices = $this->database->getNotice($uid);
        $id = $notices[0]['id'];
        
        $this->database->archiveNotice([$id], $uid);
        
        $notices = $this->database->getNotice($uid);
        $this->assertCount(0, $notices); // Archived notices might not be in getNotice which filters by del=0.
    }
}
