<?php
namespace Tests\GameEngine;

require_once __DIR__ . '/../../GameEngine/Profile.php';

use PHPUnit\Framework\TestCase;
use Profile;

class ProfileTest extends TestCase
{
    public function testProcProfileWithSitterDoesNothing()
    {
        $database = $this->getMockBuilder(\MYSQLi_DB::class)
            ->disableOriginalConstructor()
            ->getMock();
        $database->expects($this->never())->method('query');
        $database->expects($this->never())->method('submitProfile');

        $session = new class {
            public $uid = 5;
            public function isSitterSession() { return true; }
        };
        $form = $this->createMock(\Form::class);

        $profile = new Profile($database, $session, $form);
        $profile->procProfile([
            'ft' => 'p1',
            'jahr' => '2000',
            'monat' => '01',
            'tag' => '01',
            'mw' => 1,
            'ort' => 'Rome',
            'be1' => 'Right',
            'be2' => 'Left',
        ]);

        $this->addToAssertionCount(1);
    }

    public function testProcProfileUpdatesDescriptionAndRejectsXssVillageName()
    {
        $database = $this->getMockBuilder(\MYSQLi_DB::class)
            ->disableOriginalConstructor()
            ->getMock();
        $database->expects($this->once())
            ->method('submitProfile')
            ->with(5, 1, 'Rome', '2000-01-01', 'Left', 'Right');
        $database->method('getProfileVillages')->willReturn([
            ['wref' => 100],
            ['wref' => 101],
        ]);
        $database->expects($this->once())
            ->method('setVillageName')
            ->with(101, 'ValidVillage');

        $session = new class {
            public $uid = 5;
            public function isSitterSession() { return false; }
        };
        $form = $this->createMock(\Form::class);

        $profile = new Profile($database, $session, $form);
        $profile->procProfile([
            'ft' => 'p1',
            'jahr' => '2000',
            'monat' => '01',
            'tag' => '01',
            'mw' => 1,
            'ort' => 'Rome',
            'be1' => 'Right',
            'be2' => 'Left',
            'dname0' => '<script>alert(1)</script>',
            'dname1' => 'ValidVillage',
        ]);
    }
}
