<?php
namespace App\Admin\Services;

use App\Database\IDbConnection;

class EditProtectionService
{
    private IDbConnection $db;
    private string $tbPrefix;

    public function __construct(IDbConnection $db, string $tbPrefix)
    {
        $this->db = $db;
        $this->tbPrefix = $tbPrefix;
    }

    public function updateProtection(int $uid, int $days): void
    {
        $time = time();
        $protection = $days > 0 ? $time + ($days * 86400) : 0;
        $this->db->query_new("UPDATE " . $this->tbPrefix . "users SET protect = ? WHERE id = ?", $protection, $uid);
    }
    
    public function logProtectionChange(int $adminId, int $targetUid, string $username, int $days): void
    {
        $time = time();
        $targetNameSafe = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
        $logText = "Set protection for user <a href='admin.php?p=player&uid=$targetUid'>$targetNameSafe</a> to $days days";
        $this->db->query_new(
            "INSERT INTO " . $this->tbPrefix . "admin_log (`id`, `user`, `log`, `time`) VALUES (0, ?, ?, ?)",
            $adminId, $logText, $time
        );
    }
}
