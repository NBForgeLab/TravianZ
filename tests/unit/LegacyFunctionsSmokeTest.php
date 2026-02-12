<?php
declare(strict_types=1);

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class LegacyFunctionsSmokeTest extends TestCase
{
    private function gameEnginePath(string $relativePath): string
    {
        $relativePath = ltrim($relativePath, '/');

        $root = dirname(__DIR__, 2);
        $candidates = [
            $root . '/GameEngine/' . $relativePath,
            $root . '/gameengine/' . $relativePath,
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        $this->fail('Missing required file: ' . $relativePath);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testAddSubStoresReplacementInGlobalSubs(): void
    {
        require $this->gameEnginePath('functions.php');

        addSub('FOO', 'bar');

        $this->assertArrayHasKey('subs', $GLOBALS);
        $this->assertIsArray($GLOBALS['subs']);
        $this->assertSame('bar', $GLOBALS['subs']['{FOO}']);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testTemplateReplacesTokensFromArray(): void
    {
        require $this->gameEnginePath('functions.php');

        $tmpFile = tempnam(sys_get_temp_dir(), 'travianz_tpl_');
        if (!is_string($tmpFile) || $tmpFile === '') {
            $this->fail('Failed to create temp file');
        }

        file_put_contents($tmpFile, 'Hello {NAME}!');

        $rendered = template($tmpFile, ['{NAME}' => 'Alice']);

        $this->assertSame('Hello Alice!', $rendered);
    }
}

