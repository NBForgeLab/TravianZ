<?php

declare(strict_types=1);

namespace TravianZ\Tests\AdminE2E;

use Throwable;

final class TestRunner
{
    private array $tests = [];

    public function add(string $name, callable $fn): void
    {
        $this->tests[] = ['name' => $name, 'fn' => $fn];
    }

    public function run(): int
    {
        $passed = 0;
        $failed = 0;

        foreach ($this->tests as $test) {
            $name = $test['name'];
            $fn = $test['fn'];
            try {
                $fn();
                $passed++;
                $this->out("PASS  {$name}");
            } catch (Throwable $e) {
                $failed++;
                $this->out("FAIL  {$name}");
                $this->out('      ' . $e::class . ': ' . $e->getMessage());
            }
        }

        $this->out('');
        $this->out("Total: " . ($passed + $failed) . " | Passed: {$passed} | Failed: {$failed}");

        return $failed === 0 ? 0 : 1;
    }

    private function out(string $line): void
    {
        fwrite(STDOUT, $line . PHP_EOL);
    }
}

