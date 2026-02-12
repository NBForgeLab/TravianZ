<?php
declare(strict_types=1);

use App\Service\MessageService;
use PHPUnit\Framework\TestCase;

final class MessageServiceTest extends TestCase
{
    public function testPlanDeletesSplitsByOwnership(): void
    {
        $plan = MessageService::planDeletes([
            ['id' => 1, 'target' => 7, 'owner' => 7],
            ['id' => 2, 'target' => 7, 'owner' => 9],
            ['id' => 3, 'target' => 9, 'owner' => 7],
            ['id' => 4, 'target' => 9, 'owner' => 9],
        ], 7);

        $this->assertSame([2], $plan['mode5']);
        $this->assertSame([3], $plan['mode7']);
        $this->assertSame([1], $plan['mode8']);
    }
}

