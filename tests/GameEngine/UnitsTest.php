<?php
require_once __DIR__ . '/../DatabaseTestCase.php';
use Tests\DatabaseTestCase;

class UnitsTest extends DatabaseTestCase
{
    private $db;
    private $form;
    private $generator;
    private $sess;
    private $vil;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = $GLOBALS['database'];
        
        // Setup mocks
        $this->form = new class { 
            public function addError($a, $b) {} 
            public function getErrors() { return []; } 
        };
        $this->generator = $this->createMock(stdClass::class);
        $this->sess = (object) ['checker' => '123', 'tribe' => 1, 'uid' => 1, 'sit' => 0];
        $this->vil = (object) ['wid' => 1, 'unitarray' => ['u11' => 10, 'u12' => 0, 'hero' => 0], 'wid' => 1];
    }

    public function testProcUnitsInvalidType()
    {
        $units = new Units($this->db, $this->form, $this->generator, $this->sess, $this->vil);
        
        // Invalid type (c=99)
        $post = ['c' => 99];
        
        // This should not throw exception, it should just return
        $result = $units->procUnits($post);
        $this->assertNull($result);
    }
}
