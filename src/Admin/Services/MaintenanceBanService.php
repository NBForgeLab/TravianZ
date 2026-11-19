<?php
namespace App\Admin\Services;

class MaintenanceBanService {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function massBan($duration, $start, $reason, $adminId) {
        $duration = is_numeric($duration) ? (float)$duration : 0.0;
        $adminId = (int)$adminId;
        $durationSeconds = (int)round($duration * 3600);
        $startts = $start ? strtotime((string)$start) : time();
        if ($startts === false) $startts = time();
        $endts = $startts + $durationSeconds;
        if ($durationSeconds <= 0) $endts = $startts + 86400;
        
        $reasonEsc = $this->db->escape((string)$reason);
        $access = 2; // jucători normali

        $this->db->query(
            "INSERT INTO " . TB_PREFIX . "banlist (uid, name, reason, time, end, admin, active)
             SELECT id, username, '$reasonEsc', $startts, $endts, $adminId, 1
             FROM " . TB_PREFIX . "users
             WHERE access = $access AND id > 3
             ON DUPLICATE KEY UPDATE 
                reason = VALUES(reason),
                time = VALUES(time),
                end = VALUES(end),
                admin = VALUES(admin),
                active = 1"
        );
        
        $logText = "Mass ban for access=$access, duration=" . ($duration) . "h, reason='" . (string)$reason . "'";
        $logEsc = $this->db->escape($logText);
        $this->db->query(
            "INSERT INTO " . TB_PREFIX . "admin_log (`id`, `user`, `log`, `time`) " .
            "VALUES (0, '$adminId', '$logEsc', " . time() . ")"
        );
    }
}
