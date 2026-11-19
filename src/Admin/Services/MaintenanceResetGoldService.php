<?php
namespace App\Admin\Services;

class MaintenanceResetGoldService {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function resetGoldForAllUsers($adminId) {
        $this->db->query("UPDATE " . TB_PREFIX . "users SET gold = 0 WHERE id > 0");

        $time = time();
        $logText = "Reset gold to 0 for all users";
        $logEsc = $this->db->escape($logText);
        $this->db->query(
            "INSERT INTO " . TB_PREFIX . "admin_log (`id`, `user`, `log`, `time`) " .
            "VALUES (0, '$adminId', '$logEsc', $time)"
        );
    }
}
