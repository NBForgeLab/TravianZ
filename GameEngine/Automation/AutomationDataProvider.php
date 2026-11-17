<?php

namespace App\Automation;

class AutomationDataProvider {
    private $globals;
    public function __construct(array $globals) {
        $this->globals = $globals;
    }
    public function getBid($id) {
        return $this->globals['bid' . $id] ?? [];
    }
    public function getUnits() {
        return $this->globals['units'] ?? null;
    }
    public function getBattle() {
        return $this->globals['battle'] ?? null;
    }
    public function getTechnology() {
        return $this->globals['technology'] ?? null;
    }
    public function getHeroLevels() {
        return $this->globals['hero_levels'] ?? null;
    }
    public function getHeroItemCatalog() {
        return $this->globals['heroItemCatalog'] ?? null;
    }
    public function getRanking() {
        return $this->globals['ranking'] ?? null;
    }
}
