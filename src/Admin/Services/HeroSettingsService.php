<?php
namespace App\Admin\Services;

use App\Database\IDbConnection;

class HeroSettingsService
{
    private IDbConnection $db;
    private string $tbPrefix;

    public function __construct(IDbConnection $db, string $tbPrefix)
    {
        $this->db = $db;
        $this->tbPrefix = $tbPrefix;
    }

    public function updateHero(int $uid, int $hid, array $data): bool
    {
        $hnameEsc = $this->db->escape($data['name']);
        
        // Check for T4 resources
        $hHasResCols = false;
        $result = $this->db->query("SHOW COLUMNS FROM " . $this->tbPrefix . "hero LIKE 'resources'");
        if ($this->db->num_rows($result) > 0) { $hHasResCols = true; }

        $q = "UPDATE " . $this->tbPrefix . "hero SET 
                unit = " . (int)$data['unit'] . ",
                name = '$hnameEsc',
                level = " . (int)$data['level'] . ",
                points = " . (int)$data['points'] . ",
                experience = " . (int)$data['experience'] . ",
                health = '" . (float)$data['health'] . "',
                attack = " . (int)$data['attack'] . ",
                defence = " . (int)$data['defence'] . ",
                attackbonus = " . (int)$data['attackbonus'] . ",
                defencebonus = " . (int)$data['defencebonus'] . ",
                regeneration = " . (int)$data['regeneration'] . ""
            . ($hHasResCols ? ",
                resources = " . (int)$data['resources'] . ",
                res_type = " . (int)$data['res_type'] . "" : "")
            . "
              WHERE heroid = " . (int)$hid . " AND uid = " . (int)$uid;

        return $this->db->query($q);
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
