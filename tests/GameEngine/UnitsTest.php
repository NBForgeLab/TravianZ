<?php
use PHPUnit\Framework\TestCase;

class UnitsTest extends TestCase
{
    private $db;
    private $form;
    private $generator;
    private $sess;
    private $vil;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = $this->createMock(\MYSQLi_DB::class);
        
        $this->form = new class { 
            public $errors = [];
            public function addError($a, $b) { $this->errors[$a] = $b; } 
            public function getErrors() { return $this->errors; } 
            public function returnErrors() { return count($this->errors); }
        };
        $this->generator = $this->createMock(stdClass::class);
        $this->sess = (object) ['checker' => '123', 'tribe' => 1, 'uid' => 1, 'sit' => 0];
        $this->vil = (object) ['wid' => 1, 'unitarray' => ['u11' => 10, 'u12' => 0, 'hero' => 0]];
    }

    public function testProcUnitsInvalidType()
    {
        $units = new Units($this->db, $this->form, $this->generator, $this->sess, $this->vil);
        
        $post = ['c' => 99];
        $result = $units->procUnits($post);
        $this->assertNull($result);
    }

    public function testSendTroopsBackReleasesLockOnlyOnceOnValidationError()
    {
        $this->db->method('getEnforceLock')->willReturn(true);
        $this->db->method('getEnforceArray')->willReturn(['from' => 1, 'vref' => 2, 'u1' => 5, 'hero' => 0]);
        $this->db->method('getOasisEnforceArray')->willReturn(['conqured' => 0]);
        $this->db->method('getVillage')->willReturn(['owner' => 1]);
        $this->db->method('getUserField')->willReturn(1);
        $this->db->expects($this->once())->method('releaseEnforceLock')->with('k1');

        $units = new Units($this->db, $this->form, $this->generator, $this->sess, $this->vil);
        $rm = new \ReflectionMethod(Units::class, 'sendTroopsBack');
        $rm->setAccessible(true);
        $rm->invoke($units, ['ckey' => 'k1', 't1' => -1]);
        $this->assertNotEmpty($this->form->getErrors());
    }
}
