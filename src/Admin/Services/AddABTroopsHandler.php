<?php

namespace App\Admin\Services;

class AddABTroopsHandler
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

        $fields = [];
        for ($i = 1; $i <= 8; $i++) {
            $a = (int)($postData['a' . $i] ?? 0);
            $b = (int)($postData['b' . $i] ?? 0);
            $fields[] = "a$i = $a";
            $fields[] = "b$i = $b";
        }

        $q = "UPDATE " . TB_PREFIX . "abdata SET " . implode(", ", $fields) . " WHERE vref = $id";
        $this->database->query($q);

        $time = time();
        $village = $this->database->getVillage($id);
        $villageName = $village['name'] ?? 'Village';
        $villageNameSafe = htmlspecialchars($villageName, ENT_QUOTES, 'UTF-8');
        
        $logText = "Changed troop upgrade levels in village <a href='admin.php?p=village&did=$id'>$villageNameSafe</a>";
        
        $adminIdEsc = $this->database->escape((string)(int)$adminId);
        $logEsc = $this->database->escape($logText);
        
        $this->database->query(
            "INSERT INTO " . TB_PREFIX . "admin_log (`id`, `user`, `log`, `time`) " .
            "VALUES (0, '$adminIdEsc', '$logEsc', $time)"
        );
        
        return true;
    }
}
