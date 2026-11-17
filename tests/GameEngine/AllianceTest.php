<?php
namespace Tests\GameEngine;

require_once __DIR__ . '/../DatabaseTestCase.php';
require_once __DIR__ . '/../../GameEngine/Alliance.php';

use Tests\DatabaseTestCase;

class AllianceTest extends DatabaseTestCase
{
    private $alliance;

    protected function setUp(): void
    {
        parent::setUp();
        $this->alliance = new \Alliance();
        // Ensure constants are defined
        if (!defined('ADMIN')) define('ADMIN', 2);
    }

    public function testIsForumAccessibleReturnsFalseByDefault()
    {
        global $session;
        $session->sharedForums = [];
        $this->assertFalse($this->alliance->isForumAccessible(1));
    }

    public function testSendInviteFailsWithNoPermission()
    {
        global $session, $form;
        $session->uid = 1;
        $session->alliance = 1;
        $this->alliance->userPermArray = ['opt4' => 0];

        $form = new class { 
            public $errors = []; 
            public function addError($a, $b) { $this->errors[$a] = $b; }
            public function returnErrors() { return count($this->errors); }
        };

        $this->alliance->sendInvite(['a_name' => 'target']);
        $this->assertGreaterThan(0, $form->returnErrors());
        $this->assertArrayHasKey('name', $form->errors);
    }

    public function testKickAlliUserFailsWithNoPermission()
    {
        global $session, $form;
        $session->uid = 1;
        $session->alliance = 1;
        $this->alliance->userPermArray = ['opt2' => 0];

        $form = new class { 
            public $errors = []; 
            public function addError($a, $b) { $this->errors[$a] = $b; }
            public function returnErrors() { return count($this->errors); }
        };

        $this->alliance->procAlliForm(['s' => 1, 'o' => 2, 'a_user' => 2]);
        $this->assertGreaterThan(0, $form->returnErrors());
    }
}
