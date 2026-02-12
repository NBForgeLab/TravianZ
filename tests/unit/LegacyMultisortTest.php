<?php
declare(strict_types=1);

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

final class LegacyMultiSortTest extends TestCase
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
    public function testMultiKeySortWorks(): void
    {
        require $this->gameEnginePath('Multisort.php');
        $multisort = new multiSort();

        $data = [
            ['x' => 0, 'y' => 0, 'pop' => 5, 'id' => 'a'],
            ['x' => 0, 'y' => 0, 'pop' => 9, 'id' => 'b'],
            ['x' => -1, 'y' => 10, 'pop' => 1, 'id' => 'c'],
            ['x' => 0, 'y' => -1, 'pop' => 100, 'id' => 'd'],
        ];

        $sorted = $multisort->sorte($data, 'x', true, 2, 'y', true, 2, 'pop', false, 2);

        $this->assertSame(['c', 'd', 'b', 'a'], array_column($sorted, 'id'));
    }
}
