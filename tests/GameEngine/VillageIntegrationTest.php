<?php

namespace Tests\GameEngine;

require_once __DIR__ . '/../../tests/DatabaseTestCase.php';
require_once __DIR__ . '/../../GameEngine/Village.php';
require_once __DIR__ . '/../../GameEngine/Session.php';
require_once __DIR__ . '/../../GameEngine/Technology.php';
require_once __DIR__ . '/../../GameEngine/Building.php';

use Tests\DatabaseTestCase;
use Village;

class VillageIntegrationTest extends DatabaseTestCase
{
    public function testVillageInitialization()
    {
        // Mock dependencies
        $database = $GLOBALS['database'];
        $session = $this->createMock(\Session::class); 
        $technology = $this->createMock(\Technology::class);
        $bids = [1=>[], 2=>[], 3=>[], 4=>[], 5=>[], 6=>[], 7=>[], 8=>[], 9=>[], 45=>[]]; 

        // Initialize Village with DI
        $village = new Village($database, $session, $technology, $bids);

        $this->assertInstanceOf(Village::class, $village);
    }
    
    public function testTickCallsMethods()
    {
        $database = $this->createMock(\MYSQLi_DB::class);
        $session = $this->createMock(\Session::class);
        $technology = $this->createMock(\Technology::class);
        $bids = [1=>[], 2=>[], 3=>[], 4=>[], 5=>[], 6=>[], 7=>[], 8=>[], 9=>[], 45=>[]];
        
        $village = $this->getMockBuilder(Village::class)
            ->setConstructorArgs([$database, $session, $technology, $bids])
            ->onlyMethods(['calculateProduction', 'processProduction'])
            ->getMock();
            
        $village->expects($this->once())->method('calculateProduction');
        $village->expects($this->once())->method('processProduction');
        
        $village->tick();
    }
}
