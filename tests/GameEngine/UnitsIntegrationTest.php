<?php
use PHPUnit\Framework\TestCase;

class UnitsIntegrationTest extends TestCase
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
        $this->db = $this->createMock(\MYSQLi_DB::class);

        $this->form = new class {
            public $errors = [];
            public function addError($a, $b) { $this->errors[$a] = $b; }
            public function getErrors() { return $this->errors; }
            public function returnErrors() { return count($this->errors); }
        };

        $this->generator = new class {
            public function procDistanceTime($a, $b, $c, $d, $e) { return 100; }
            public function getBaseID($x, $y) { return 1; }
        };

        $this->sess = (object) ['uid' => 1, 'tribe' => 1, 'sit' => 0];
        $this->vil = (object) ['wid' => 1, 'unitarray' => ['u11' => 10, 'u12' => 0, 'hero' => 0]];
        $this->technology = new \stdClass();
        $this->unitData = [];
        $this->building = new \stdClass();
    }

    public function testSendTroopsFailsWithInvalidChecksum()
    {
        $units = new Units($this->db, $this->form, $this->generator, $this->sess, $this->vil, $this->technology, $this->unitData, $this->building);

        $post = [
            'c' => 1,
            'a' => 533374,
            'timestamp_checksum' => 'invalid_checksum'
        ];

        $units->procUnits($post);
        $this->assertGreaterThan(0, $this->form->returnErrors());
        $this->assertArrayHasKey('error', $this->form->getErrors());
    }
}
