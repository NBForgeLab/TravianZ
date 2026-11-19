<?php
namespace App\Admin\Services;

use App\Database\IDbConnection;

class EditPlusService
{
    private IDbConnection $db;
    private string $tbPrefix;
    
    public function __construct(IDbConnection $db, string $tbPrefix)
    {
        $this->db = $db;
        $this->tbPrefix = $tbPrefix;
    }

    public function updatePlusBonuses(int $uid, array $bonusData): void
    {
        $this->db->updateUserField($uid, array_keys($bonusData), array_values($bonusData), 1);
    }
    
    public function logPlusChange(int $adminId, int $targetUid, string $logText): void
    {
        $time = time();
        $this->db->query_new(
            "INSERT INTO " . $this->tbPrefix . "admin_log (`id`, `user`, `log`, `time`) VALUES (0, ?, ?, ?)",
            $adminId, $logText, $time
        );
    }
}
