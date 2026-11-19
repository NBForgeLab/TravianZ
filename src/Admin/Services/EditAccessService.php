<?php
namespace App\Admin\Services;

use App\Database\IDbConnection;

class EditAccessService
{
    private IDbConnection $db;
    private string $tbPrefix;

    public function __construct(IDbConnection $db, string $tbPrefix)
    {
        $this->db = $db;
        $this->tbPrefix = $tbPrefix;
    }

    public function updateUserAccess(int $uid, int $access): void
    {
        $this->db->query("UPDATE " . $this->tbPrefix . "users SET access = " . (int)$access . " WHERE id = " . (int)$uid);
    }
}
