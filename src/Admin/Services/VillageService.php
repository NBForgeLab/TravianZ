<?php

namespace App\Admin\Services;

class VillageService
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function updateVillageBuildings(int $villageId, array $buildings, int $adminId): void
    {
        $sets = [];
        for ($i = 1; $i <= 40; $i++) {
            $lvl = (int)($buildings["id{$i}level"] ?? 0);
            $gid = (int)($buildings["id{$i}gid"] ?? 0);
            $sets[] = "f$i = $lvl, f{$i}t = $gid";
        }
        $setSql = implode(", ", $sets);
        $this->database->query("UPDATE " . TB_PREFIX . "fdata SET $setSql WHERE vref = $villageId");
        
        $logText = "Recalculated buildings for village $villageId";
        $logEsc = $this->database->escape($logText);
        $this->database->query(
            "INSERT INTO " . TB_PREFIX . "admin_log (`id`, `user`, `log`, `time`) " .
            "VALUES (0, '$adminId', '$logEsc', " . time() . ")"
        );
    }

    public function renameVillage(int $villageId, string $newName, int $adminId): void
    {
        $nameEsc = $this->database->escape($newName);
        $this->database->query("UPDATE " . TB_PREFIX . "vdata SET name = '$nameEsc' WHERE wref = $villageId");
        
        $logText = "Renamed village <a href='admin.php?p=village&did=$villageId'>$villageId</a> to '$newName'";
        $logEsc = $this->database->escape($logText);
        $this->database->query(
            "INSERT INTO " . TB_PREFIX . "admin_log (`id`, `user`, `log`, `time`) " .
            "VALUES (0, '$adminId', '$logEsc', " . time() . ")"
        );
    }
}
