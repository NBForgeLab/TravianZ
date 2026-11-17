<?php
class UnitDataService {
    private $units = [];

    public function __construct() {
        // Load the file that defines $u1, $u2, etc.
        include(__DIR__ . '/Data/unitdata.php');
        
        // Collect all $u variables
        $i = 1;
        while (isset(${'u' . $i})) {
            $this->units[$i] = ${'u' . $i};
            $i++;
        }
        // Also add u99 if it exists
        if (isset($u99)) {
            $this->units[99] = $u99;
        }
    }

    public function getUnitData($unitId) {
        return $this->units[$unitId] ?? null;
    }
}
