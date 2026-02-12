<?php
declare(strict_types=1);

use App\Legacy\MultiSorter;
use PHPUnit\Framework\TestCase;

final class MultiSorterTest extends TestCase
{
    public function testSortsByMultipleKeys(): void
    {
        $items = [
            ['x' => 0, 'y' => 0, 'pop' => 5, 'id' => 'a'],
            ['x' => 0, 'y' => 0, 'pop' => 9, 'id' => 'b'],
            ['x' => -1, 'y' => 10, 'pop' => 1, 'id' => 'c'],
            ['x' => 0, 'y' => -1, 'pop' => 100, 'id' => 'd'],
        ];

        $sorted = MultiSorter::sort($items, [
            ['x', true, 2],
            ['y', true, 2],
            ['pop', false, 2],
        ]);

        $this->assertSame(['c', 'd', 'b', 'a'], array_column($sorted, 'id'));
    }
}

