<?php
namespace Tests\GameEngine;

use PHPUnit\Framework\TestCase;

class AllianceTest extends TestCase
{
    private $alliance;
    private $mockDatabase;
    private $mockSession;
    private $mockForm;

    protected function setUp(): void
    {
        parent::setUp();
        if (!defined('ADMIN')) define('ADMIN', 2);
        if (!defined('SERVER')) define('SERVER', 'http://localhost/');
        
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

    public function testQuitAllyAsLeaderWithPromotion()
    {
        // Mock dependencies
        $this->mockDatabase->method('isAllianceOwner')->willReturn(1);
        $this->mockDatabase->method('countAllianceMembers')->willReturn(2);
        
        $member = ['id' => 2, 'username' => 'newLeader'];
        $this->mockDatabase->method('getAllMember')->willReturn([
            ['id' => 1, 'username' => 'leader'],
            $member
        ]);
        
        // Ensure promoteNewAllianceLeader is called
        $this->mockDatabase->expects($this->once())
            ->method('promoteNewAllianceLeader');
            
        // Mock user password verification
        $this->mockSession->userinfo = ['password' => password_hash('password123', PASSWORD_DEFAULT)];
        
        // Use a output buffer to swallow header() calls
        ob_start();
        $this->alliance->procAlliForm([
            's' => 1, 
            'o' => 11, 
            'pw' => 'password123',
            'new_founder' => 2
        ]);
        $output = ob_get_clean();
        
        $this->assertEquals(0, $this->mockForm->returnErrors());
    }

    public function testKickAlliUserDeletesAllianceIfEmpty()
    {
        // Mock permissions
        $this->alliance->userPermArray = ['opt2' => 1];
        
        // Mock other DB calls
        $this->mockDatabase->method('isAllianceOwner')->willReturn(0);
        $this->mockDatabase->method('getUserArray')->willReturn(['id' => 2, 'username' => 'target']);
        $this->mockDatabase->method('getUserField')->willReturn(1);
        
        // Ensure deleteAlliance is called
        $this->mockDatabase->expects($this->once())
            ->method('deleteAlliance');
            
        ob_start();
        $this->alliance->procAlliForm(['s' => 1, 'o' => 2, 'a_user' => 2]);
        $output = ob_get_clean();
        
        $errors = $this->mockForm->getErrors();
        $this->assertArrayHasKey('perm', $errors);
        $this->assertStringContainsString('target', $errors['perm']);
    }
}
