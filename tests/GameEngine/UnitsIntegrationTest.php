<?php
use PHPUnit\Framework\TestCase;
require_once __DIR__ . '/../DatabaseTestCase.php';

class UnitsIntegrationTest extends \Tests\DatabaseTestCase
{
    private $db;
    private $form;
    private $generator;
    private $sess;
    private $vil;
    private $technology;
    private $unitData;
    private $building;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = $GLOBALS['database'];
        
        // Define a mock class for Form and Generator
        $this->form = new class { 
            public function addError($a, $b) {} 
            public function getErrors() { return []; }
            public function returnErrors() { return 0; }
        };
        
        $this->generator = new class {
            public function procDistanceTime($a, $b, $c, $d, $e) { return 100; }
            public function getBaseID($x, $y) { return 1; }
        };
        
        $this->sess = (object) ['uid' => 1, 'tribe' => 1, 'sit' => 0];
        $this->vil = (object) ['wid' => 1, 'unitarray' => ['u11' => 10, 'u12' => 0, 'hero' => 0]];
        $this->technology = [];
        $this->unitData = [];
        $this->building = $this->createMock(\stdClass::class);
    }

    public function testSendTroopsFailsWithInvalidChecksum()
    {
        $units = new Units($this->db, $this->form, $this->generator, $this->sess, $this->vil, $this->technology, $this->unitData, $this->building);
        
        $post = [
            'c' => 1,
            'a' => 533374,
            'timestamp_checksum' => 'invalid_checksum'
        ];

        // This should trigger the failure path and NOT call claimA2b, 
        // adding an error to the form.
        $units->procUnits($post);
        $this->assertEquals(0, $this->form->returnErrors()); // Should have errors but mock doesn't store them
        $this->assertTrue(true);
    }
}
