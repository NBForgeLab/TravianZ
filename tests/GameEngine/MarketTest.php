<?php
namespace Tests;

require_once __DIR__ . '/../DatabaseTestCase.php';
require_once __DIR__ . '/../../GameEngine/Market.php';

class MarketTest extends DatabaseTestCase
{
    private $db;
    private $market;
    private $village;
    private $building;

    protected function setUp(): void
    {
        parent::setUp();
        if (!defined('TRADER_CAPACITY')) define('TRADER_CAPACITY', 1);
        if (!defined('USER_ON_VACATION')) define('USER_ON_VACATION', 'User is on vacation');
        require_once __DIR__ . '/../../GameEngine/Lang/en.php';
        
        $this->db = $GLOBALS['database'];
        // Mock village
        $this->village = (object) ['wid' => 1, 'resarray' => [], 'coor' => ['x' => 0, 'y' => 0]];
        $this->building = new class { public function getTypeLevel($id) { return 1; } };
        
        $GLOBALS['building'] = $this->building;
        $GLOBALS['village'] = $this->village;
        $GLOBALS['session'] = (object) ['uid' => 1, 'tribe' => 1, 'mchecker' => 'test'];
        $GLOBALS['generator'] = new class { public function procDistanceTime($a, $b, $c, $d) { return 100; } };
        
        $this->market = new \Market($this->db, $this->village, $GLOBALS['session'], $this->building, [], $GLOBALS['generator']);
    }

    public function testMerchantAvail()
    {
        $this->market->merchant = 5;
        $this->market->used = 2;
        
        $this->assertEquals(3, $this->market->merchantAvail());
    }

    public function testProcMarketSendResourceValidationError()
    {
        // Mock global session and form
        $GLOBALS['session'] = (object) ['goldclub' => false];
        $GLOBALS['form'] = new class { 
            public $errors = []; 
            public function addError($a, $b) { $this->errors[] = $b; } 
            public function getErrors() { return $this->errors; } 
        };
        
        // Post data
        $post = ['ft' => 'mk1', 'send3' => 4]; // Invalid merchants
        
        $this->market->procMarket($post);
        
        $this->assertNotEmpty($GLOBALS['form']->getErrors());
    }
}
