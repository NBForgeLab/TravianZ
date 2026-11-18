<?php
use PHPUnit\Framework\TestCase;

class TechnologyTestRefactored extends TestCase {
    public function testGrabAcademyRes() {
        $village = new stdClass();
        $village->researching = [['tech' => 't1'], ['tech' => 'a1']];
        
        $tech = new Technology(
            null, 
            $village, 
            $this->createMock(\Session::class), 
            $this->createMock(\UnitDataService::class),
            $this->createMock(\Building::class),
            [],
            null
        );
        $result = $tech->grabAcademyRes();
        
        $this->assertCount(1, $result);
        $this->assertEquals('t1', $result[0]['tech']);
    }
}
