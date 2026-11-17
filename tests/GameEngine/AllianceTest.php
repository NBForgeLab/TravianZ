<?php
namespace Tests\GameEngine;

require_once __DIR__ . '/../../autoloader.php';
require_once __DIR__ . '/../DatabaseTestCase.php';
require_once __DIR__ . '/../../GameEngine/Alliance.php';
require_once __DIR__ . '/../../GameEngine/Database.php';

use Tests\DatabaseTestCase;

class AllianceTest extends DatabaseTestCase
{
    private $alliance;

    protected function setUp(): void
    {
        parent::setUp();
        if (!defined('ADMIN')) define('ADMIN', 2);
        
        // Mock the Database class using the actual class name to allow method mocking
        $this->mockDatabase = $this->getMockBuilder(\MYSQLi_DB::class)
            ->disableOriginalConstructor()
            ->getMock();
            
        $this->mockSession = new \stdClass();
        $this->mockSession->uid = 1;
        $this->mockSession->alliance = 1;
        $this->mockSession->username = 'test';
        $this->mockSession->sharedForums = [];
        
        $this->mockForm = new class { 
            public $errors = []; 
            public function addError($a, $b) { $this->errors[$a] = $b; }
            public function returnErrors() { return count($this->errors); }
            public function getErrors() { return $this->errors; }
        };
        
        $this->alliance = new \Alliance($this->mockDatabase, $this->mockSession, $this->mockForm);
    }

    public function testIsForumAccessibleReturnsFalseByDefault()
    {
        $this->assertFalse($this->alliance->isForumAccessible(1));
    }

    public function testSendInviteFailsWithNoPermission()
    {
        $this->alliance->userPermArray = ['opt4' => 0];

        $this->alliance->sendInvite(['a_name' => 'target']);
        $this->assertGreaterThan(0, $this->mockForm->returnErrors());
        $this->assertArrayHasKey('name', $this->mockForm->errors);
    }

    public function testKickAlliUserFailsWithNoPermission()
    {
        $this->alliance->userPermArray = ['opt2' => 0];

        $this->alliance->procAlliForm(['s' => 1, 'o' => 2, 'a_user' => 2]);
        $this->assertGreaterThan(0, $this->mockForm->returnErrors());
    }
}
