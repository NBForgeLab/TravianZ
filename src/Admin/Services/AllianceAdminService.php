<?php
namespace App\Admin\Services;

use App\Database\IDbConnection;

class AllianceAdminService
{
    private IDbConnection $db;
    private string $tbPrefix;

    public function __construct(IDbConnection $db, string $tbPrefix)
    {
        $this->db = $db;
        $this->tbPrefix = $tbPrefix;
    }

    public function deleteAlliance(int $aid): void
    {
        // 1. Scoate toți membrii
        $this->db->query("UPDATE " . $this->tbPrefix . "users SET alliance = 0 WHERE alliance = " . (int)$aid);

        // 2. Șterge structura alianței
        $this->db->query("DELETE FROM " . $this->tbPrefix . "alidata WHERE id = " . (int)$aid);
        $this->db->query("DELETE FROM " . $this->tbPrefix . "ali_permission WHERE alliance = " . (int)$aid);
        $this->db->query("DELETE FROM " . $this->tbPrefix . "ali_invite WHERE alliance = " . (int)$aid);
        $this->db->query("DELETE FROM " . $this->tbPrefix . "ali_log WHERE aid = " . (int)$aid);

        // 3. Șterge diplomația
        $this->db->query("DELETE FROM " . $this->tbPrefix . "diplomacy WHERE alli1 = " . (int)$aid . " OR alli2 = " . (int)$aid);

        // 4. Șterge forumul
        $this->db->query("DELETE p FROM " . $this->tbPrefix . "forum_post p 
            INNER JOIN " . $this->tbPrefix . "forum_topic t ON p.topic = t.id 
            WHERE t.alliance = " . (int)$aid);

        $this->db->query("DELETE FROM " . $this->tbPrefix . "forum_topic WHERE alliance = " . (int)$aid);
        $this->db->query("DELETE FROM " . $this->tbPrefix . "forum_cat WHERE alliance = " . (int)$aid);
    }

    public function logAdminAction(int $admid, string $logText): void
    {
        $time = time();
        $logEsc = $this->db->escape($logText);
        $this->db->query(
            "INSERT INTO " . $this->tbPrefix . "admin_log (`id`, `user`, `log`, `time`) " .
            "VALUES (0, '" . (int)$admid . "', '$logEsc', $time)"
        );
    }

    public function deleteAllianceMedal(int $medalid, int $aid): void
    {
        $this->db->query("UPDATE " . $this->tbPrefix . "allimedal SET del = 1 WHERE id = " . (int)$medalid . " AND allyid = " . (int)$aid);
    }

    public function deleteAllianceMedalsByAllyId(int $aid): int
    {
        $this->db->query("UPDATE " . $this->tbPrefix . "allimedal SET del = 1 WHERE allyid = " . (int)$aid . " AND del = 0");
        return (int)$this->db->affected_rows();
    }

    public function deleteAllianceMedalsByWeek(int $week): int
    {
        $this->db->query("UPDATE " . $this->tbPrefix . "allimedal SET del = 1 WHERE week = " . (int)$week . " AND del = 0");
        return (int)$this->db->affected_rows();
    }
}
