<?php
namespace App\Admin\Services;

class MaintenanceCleanBanDataService {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function cleanBanList($adminId) {
        $this->db->query("TRUNCATE TABLE " . TB_PREFIX . "banlist");

        $time = time();
        $logText = "Cleared banlist (TRUNCATE)";
        $logEsc = $this->db->escape($logText);
        $this->db->query(
            "INSERT INTO " . TB_PREFIX . "admin_log (`id`, `user`, `log`, `time`) " .
            "VALUES (0, '$adminId', '$logEsc', $time)"
        );
    }
}
