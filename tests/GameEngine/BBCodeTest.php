<?php
namespace Tests\GameEngine;

use PHPUnit\Framework\TestCase;

class BBCodeTest extends TestCase
{
    private $savedDatabase;
    private $savedGenerator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->savedDatabase = $GLOBALS['database'] ?? null;
        $this->savedGenerator = $GLOBALS['generator'] ?? null;
        require_once __DIR__ . '/../../GameEngine/Data/unitdata.php';
        require_once __DIR__ . '/../../GameEngine/Lang/en.php';
    }

    protected function tearDown(): void
    {
        $GLOBALS['database'] = $this->savedDatabase;
        $GLOBALS['generator'] = $this->savedGenerator;
        parent::tearDown();
    }

    private function renderBBCode(string $rawInput): string
    {
        $input = $rawInput;
        include __DIR__ . '/../../GameEngine/BBCode.php';
        return $bbcoded;
    }

    public function testFormatsBasicTagsResourcesUnitsAndSmilies()
    {
        $html = $this->renderBBCode('[message][b]Bold[/b] [i]Ital[/i] [u]Under[/u] [lumber] [tid1] :D[/message]');

        $this->assertSame(
            "<b>Bold</b> <i>Ital</i> <u>Under</u> <img src='img/x.gif' class='r1' title='" . LUMBER . "' alt='" . LUMBER . "'> <img class='unit u1' src='img/x.gif' title='" . U1 . "' alt='" . U1 . "'> <img class='smiley grin' src='img/x.gif' alt=':D' title=':D'>",
            $html
        );
    }

    public function testResolvesPlayerAllianceReportAndCoordinateCallbacks()
    {
        $db = $this->getMockBuilder(\MYSQLi_DB::class)->disableOriginalConstructor()->getMock();
        $db->method('getAllianceName')->willReturnMap([
            ['12', 'Testers'],
            ['99', ''],
        ]);
        $db->method('getUserField')->willReturn('PlayerOne');
        $db->method('getNotice2')->willReturn(['topic' => 'Scout Report']);
        $db->method('getVilWref')->willReturn(55);
        $db->method('getVillageType')->willReturn(3);
        $db->method('getVillageState')->willReturn(true);
        $db->method('getVillageField')->willReturn('Capital');
        $GLOBALS['database'] = $db;

        $GLOBALS['generator'] = new class {
            public function getMapCheck($wref) { return 'ab'; }
        };

        $html = $this->renderBBCode('[alliance]12[/alliance] | [alliance]99[/alliance] | [player]7[/player] | [report]8[/report] | [coor]10|-5[/coor]');

        $this->assertStringContainsString("<a href='allianz.php?aid=12'>Testers</a>", $html);
        $this->assertStringContainsString('Alliance not found!', $html);
        $this->assertStringContainsString("<a href='spieler.php?uid=7'>PlayerOne</a>", $html);
        $this->assertStringContainsString("<a href='berichte.php?id=8'>Scout Report</a>", $html);
        $this->assertStringContainsString("<a href='karte.php?d=55&amp;c=ab'>Capital (10|-5)</a>", $html);
    }
}
