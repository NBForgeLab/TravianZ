<?php
namespace Tests;

require_once __DIR__ . '/../GameEngine/Multisort.php';

use PHPUnit\Framework\TestCase;
use multiSort;

class MultisortTest extends TestCase
{
    private $sorter;

    protected function setUp(): void
    {
        $this->sorter = new multiSort();
    }

    public function testSortKeysAppliedLexicographically()
    {
        $rankings = [
            ['id' => 'a', 'x' => 0, 'y' => 0, 'pop' => 1],
            ['id' => 'b', 'x' => 1, 'y' => 0, 'pop' => 9],
            ['id' => 'c', 'x' => 0, 'y' => 1, 'pop' => 5],
        ];

        $result = $this->sorter->sorte(
            $rankings,
            'x', true, 2,
            'y', true, 2,
            'pop', false, 2
        );

        $this->assertEquals(['a', 'c', 'b'], array_column($result, 'id'));
    }

    public function testNumericSorting()
    {
        $offers = [
            ['id' => 'slow', 'duration' => 30],
            ['id' => 'fast', 'duration' => 5],
            ['id' => 'medium', 'duration' => 15],
        ];

        $result = $this->sorter->sorte($offers, 'duration', true, 2);
        $this->assertEquals(['fast', 'medium', 'slow'], array_column($result, 'id'));
    }

    public function testCaseInsensitiveNaturalSorting()
    {
        $naturalNames = [
            ['id' => 1, 'name' => 'Village10'],
            ['id' => 2, 'name' => 'village2'],
            ['id' => 3, 'name' => 'Village1'],
        ];

        $result = $this->sorter->sorte($naturalNames, 'name', true, 1);
        $this->assertEquals(['Village1', 'village2', 'Village10'], array_column($result, 'name'));
    }

    public function testEqualKeysKeepExistingRelativeOrder()
    {
        $ties = [
            ['id' => 'first', 'score' => 10],
            ['id' => 'second', 'score' => 10],
        ];

        $result = $this->sorter->sorte($ties, 'score', true, 2);
        $this->assertEquals(['first', 'second'], array_column($result, 'id'));
    }

    public function testEmptySortCriteriaReturnsInputArray()
    {
        $rankings = [
            ['id' => 'a', 'x' => 0, 'y' => 0, 'pop' => 1],
            ['id' => 'b', 'x' => 1, 'y' => 0, 'pop' => 9],
        ];

        $this->assertEquals($rankings, $this->sorter->sorte($rankings));
    }
}
