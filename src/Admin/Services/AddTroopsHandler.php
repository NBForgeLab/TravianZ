<?php

namespace App\Admin\Services;

class AddTroopsHandler
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function handle(array $postData, $adminId)
    {
        $id = (int)($postData['id'] ?? 0);
        if ($id <= 0) {
            return false;
        }

        $village = $this->database->getVillage($id);
        if (empty($village) || !isset($village['owner'])) {
            return false;
        }
        $user = $this->database->getUserArray($village['owner'], 1);
        $tribe = (int)($user['tribe'] ?? 0);
        if ($tribe < 1) {
            return false;
        }
        $u = ($tribe - 1) * 10;

        $fields = [];
        for ($i = 1; $i <= 10; $i++) {
            $unitId = $u + $i;
            $val = (int)($postData['u' . $unitId] ?? 0);
            $fields[] = "u$unitId = $val";
        }

        $q = "UPDATE " . TB_PREFIX . "units SET " . implode(", ", $fields) . " WHERE vref = $id";
        $this->database->query($q);

        $time = time();
        $villageName = $village['name'] ?? 'Village';
        $villageNameSafe = htmlspecialchars($villageName, ENT_QUOTES, 'UTF-8');
        
        $logText = "Changed troop amounts in village <a href='admin.php?p=village&did=$id'>$villageNameSafe</a>";
        
        $adminIdEsc = $this->database->escape((string)(int)$adminId);
        $logEsc = $this->database->escape($logText);
        
        $this->database->query(
            "INSERT INTO " . TB_PREFIX . "admin_log (`id`, `user`, `log`, `time`) " .
            "VALUES (0, '$adminIdEsc', '$logEsc', $time)"
        );
        
        $this->database->addStarvationData($id);
        return true;
    }
}
