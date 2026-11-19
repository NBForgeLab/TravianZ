<?php
namespace App\Admin\Services;

use App\Database\IDbConnection;

class EditAdminInfoService
{
    private IDbConnection $db;
    private string $tbPrefix;

    public function __construct(IDbConnection $db, string $tbPrefix)
    {
        $this->db = $db;
        $this->tbPrefix = $tbPrefix;
    }

    public function logAdminAction(int $admid, string $logText): void
    {
        $time = time();
        $logEsc = $this->db->escape($logText);
        $this->db->query(
            "INSERT INTO " . $this->tbPrefix . "admin_log (`id`, `user`, `log`, `time`) " .
            "VALUES (0, " . (int)$admid . ", '$logEsc', $time)"
        );
    }
}
