<?php

namespace App\Admin\Services;

class EditBuildingsHandler
{
    private $database;
    private $automation;

    public function __construct($database, $automation)
    {
        $this->database = $database;
        $this->automation = $automation;
    }

    public function handle(array $postData, $adminId)
    {
        $adminId = (int)$adminId;
        $id = (int)($postData['id'] ?? 0);
        if ($id <= 0) {
            return false;
        }

        $sets = [];
        for ($i = 1; $i <= 40; $i++) {
            $level = (int)($postData["id{$i}level"] ?? 0);
            $gid   = (int)($postData["id{$i}gid"] ?? 0);
            $level = max(0, min(20, $level));
            $gid   = max(0, min(50, $gid));
            $sets[] = "f{$i} = $level";
            $sets[] = "f{$i}t = $gid";
        }

        $level99 = (int)($postData['id99level'] ?? 0);
        $gid99   = (int)($postData['id99gid'] ?? 0);

        if ($gid99 == 40) {
            $level99 = max(0, min(100, $level99));
        } else {
            $level99 = max(0, min(20, $level99));
        }
        $gid99 = max(0, min(50, $gid99));

        $sets[] = "f99 = " . $level99;
        $sets[] = "f99t = " . $gid99;

        $setSql = implode(', ', $sets);
        $this->database->query("UPDATE " . TB_PREFIX . "fdata SET $setSql WHERE vref = $id");

        $pop = $this->automation->recountPop($id);
        
        $fdata = $this->database->getResourceLevel($id);
        if ((int)$fdata['f99t'] === 40) {
            $wwLevel = (int)$fdata['f99'];
            if ($wwLevel > 0) {
                $wwPop = $this->automation->buildingPOP(40, $wwLevel);
                $pop += $wwPop;
                $this->database->query("UPDATE " . TB_PREFIX . "vdata SET pop = $pop WHERE wref = $id");
            }
        }

        $time = time();
        $village = $this->database->getVillage($id);
        $villageName = $village['name'] ?? 'Village';
        $villageNameSafe = htmlspecialchars($villageName, ENT_QUOTES, 'UTF-8');
        
        $log = $this->database->escape("Edited buildings for village <a href='admin.php?p=village&did=$id'>$villageNameSafe</a>");
        $this->database->query("INSERT INTO " . TB_PREFIX . "admin_log (`id`,`user`,`log`,`time`) VALUES (0,'$adminId','$log',$time)");
        
        return true;
    }
}
