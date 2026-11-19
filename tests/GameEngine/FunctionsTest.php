<?php
namespace Tests\GameEngine;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../GameEngine/functions.php';

class FunctionsTest extends TestCase
{
    private $tmpFile;
    private $savedSubs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->savedSubs = $GLOBALS['subs'] ?? null;
        unset($GLOBALS['subs']);
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'tz_tpl_');
    }

    protected function tearDown(): void
    {
        if ($this->tmpFile && file_exists($this->tmpFile)) {
            @unlink($this->tmpFile);
        }
        $GLOBALS['subs'] = $this->savedSubs;
        parent::tearDown();
    }

    public function testTemplateSubstitutesGlobalAndLocalPlaceholdersWithoutEval()
    {
        file_put_contents($this->tmpFile, 'Hello {NAME}, level {LVL}. <?php echo "PWN"; ?>');
        \addSub('NAME', 'GlobalUser');
        \addSub('LVL', '5');

        $rendered = \template($this->tmpFile, ['{LVL}' => '99']);

        $this->assertSame('Hello GlobalUser, level 99. <?php echo "PWN"; ?>', $rendered);
    }

    public function testTemplateReturnsFalseForMissingFile()
    {
        ob_start();
        $res = \template(__DIR__ . '/nonexistent_template_999.tpl');
        $out = ob_get_clean();

        $this->assertFalse($res);
        $this->assertStringContainsString('not found', $out);
    }
}
