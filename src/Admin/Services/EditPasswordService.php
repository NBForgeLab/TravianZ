<?php
namespace App\Admin\Services;

use App\Database\IDbConnection;

class EditPasswordService
{
    private IDbConnection $db;
    private string $tbPrefix;

    public function __construct(IDbConnection $db, string $tbPrefix)
    {
        $this->db = $db;
        $this->tbPrefix = $tbPrefix;
    }

    public function updatePassword(int $uid, string $newpw): void
    {
        $pass = password_hash($newpw, PASSWORD_BCRYPT, ['cost' => 12]);
        $this->db->query_new("UPDATE " . $this->tbPrefix . "users SET password = ? WHERE id = ?", $pass, $uid);
    }
    
    public function logPasswordChange(int $adminId, int $targetUid, string $username): void
    {
        $time = time();
        $targetNameSafe = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
        $logText = "Changed password for user <a href='admin.php?p=player&uid=$targetUid'>$targetNameSafe</a>";
        $this->db->query_new(
            "INSERT INTO " . $this->tbPrefix . "admin_log (`id`, `user`, `log`, `time`) VALUES (0, ?, ?, ?)",
            $adminId, $logText, $time
        );
    }
}
