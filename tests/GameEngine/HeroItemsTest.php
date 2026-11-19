<?php
namespace Tests\GameEngine;

require_once __DIR__ . '/../DatabaseTestCase.php';
require_once __DIR__ . '/../../GameEngine/HeroItems.php';

use Tests\DatabaseTestCase;

class HeroItemsTest extends DatabaseTestCase
{
    public function testZeroArgAndInjectedConstructorsBothWork()
    {
        $hiDefault = new \HeroItems();
        $hiInjected = new \HeroItems($GLOBALS['database']);

        $this->assertSame([], $hiDefault->getInventory(0));
        $this->assertSame([], $hiInjected->getInventory(0));
        $this->assertSame(0, $hiInjected->getSilver(0));
    }

    public function testGetEquippedReturnsOnlyEquippedSlotMap()
    {
        $hi = new \HeroItems($GLOBALS['database']);
        $this->assertIsArray($hi->getEquipped(999999));
        $this->assertSame([], $hi->getEquipped(999999));
    }
}
