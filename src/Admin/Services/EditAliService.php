<?php
namespace App\Admin\Services;

use App\Database\IDbConnection;

class EditAliService
{
    private IDbConnection $db;
    private string $tbPrefix;

    public function __construct(IDbConnection $db, string $tbPrefix)
    {
        $this->db = $db;
        $this->tbPrefix = $tbPrefix;
    }

    public function updateAlliance(int $aid, array $data): void
    {
        $tag = $this->db->escape(substr(trim($data['tag'] ?? ''), 0, 8));
        $name = $this->db->escape(substr(trim($data['name'] ?? ''), 0, 25));
        $leader = (int)($data['leader'] ?? 0);
        $max = (int)($data['max'] ?? 0);
        $max = max(3, min(60, $max));
        $notice = $this->db->escape($data['notice'] ?? '');
        $desc = $this->db->escape($data['desc'] ?? '');

        $this->db->query(
            "UPDATE " . $this->tbPrefix . "alidata SET 
                tag = '$tag',
                name = '$name',
                leader = $leader,
                `max` = $max,
                notice = '$notice',
                `desc` = '$desc'
             WHERE id = " . (int)$aid
        );
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
