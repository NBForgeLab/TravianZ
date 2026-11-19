<?php

namespace App\Admin\Services;

class DeleteUserHandler
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function handle($uid, $admid)
    {
        $uid = (int)$uid;
        $admid = (int)$admid;
        $target = $this->database->getUserArray($uid, 1);
        if (!$target) { return false; }
        $rawUsername = (string)($target['username'] ?? '');

        $vilIds = [];
        $res = $this->database->query("SELECT wref FROM " . TB_PREFIX . "vdata WHERE owner = $uid");
        if ($res instanceof \mysqli_result) {
            while ($r = mysqli_fetch_assoc($res)) { $vilIds[] = (int)$r['wref']; }
        } elseif (is_iterable($res)) {
            foreach ($res as $r) { $vilIds[] = (int)$r['wref']; }
        }
        $ids = $vilIds ? implode(',', $vilIds) : '0';

        if ($ids !== '0') {
            $this->database->query("UPDATE " . TB_PREFIX . "wdata SET occupied = 0 WHERE id IN ($ids)");
            $this->database->query("DELETE FROM " . TB_PREFIX . "vdata WHERE wref IN ($ids)");
            $this->database->query("DELETE FROM " . TB_PREFIX . "fdata WHERE vref IN ($ids)");
            $this->database->query("DELETE FROM " . TB_PREFIX . "bdata WHERE wid IN ($ids)");
            $this->database->query("DELETE FROM " . TB_PREFIX . "abdata WHERE vref IN ($ids)");
            $this->database->query("DELETE FROM " . TB_PREFIX . "tdata WHERE vref IN ($ids)");
            $this->database->query("DELETE FROM " . TB_PREFIX . "units WHERE vref IN ($ids)");
            $this->database->query("DELETE FROM " . TB_PREFIX . "training WHERE vref IN ($ids)");
            $this->database->query("DELETE FROM " . TB_PREFIX . "research WHERE vref IN ($ids)");
            $this->database->query("DELETE FROM " . TB_PREFIX . "demolition WHERE vref IN ($ids)");
            $this->database->query("DELETE FROM " . TB_PREFIX . "build_log WHERE wid IN ($ids)");
            $this->database->query("DELETE FROM " . TB_PREFIX . "tech_log WHERE wid IN ($ids)");
            $this->database->query("DELETE FROM " . TB_PREFIX . "market WHERE vref IN ($ids)");
            $this->database->query("DELETE FROM " . TB_PREFIX . "movement WHERE `from` IN ($ids) OR `to` IN ($ids)");
            $this->database->query("DELETE FROM " . TB_PREFIX . "attacks WHERE vref IN ($ids)");
            $this->database->query("DELETE FROM " . TB_PREFIX . "enforcement WHERE vref IN ($ids) OR `from` IN ($ids)");
            $this->database->query("DELETE FROM " . TB_PREFIX . "prisoners WHERE wref IN ($ids) OR `from` IN ($ids)");
            $this->database->query("DELETE FROM " . TB_PREFIX . "route WHERE wid IN ($ids)");
            $this->database->query("DELETE FROM " . TB_PREFIX . "ww_attacks WHERE vid IN ($ids)");

            $fl = $this->database->query("SELECT id FROM " . TB_PREFIX . "farmlist WHERE wref IN ($ids)");
            $flIds = [];
            if ($fl instanceof \mysqli_result) {
                while ($f = mysqli_fetch_assoc($fl)) { $flIds[] = (int)$f['id']; }
            } elseif (is_iterable($fl)) {
                foreach ($fl as $f) { $flIds[] = (int)$f['id']; }
            }
            if ($flIds) {
                $fids = implode(',', $flIds);
                $this->database->query("DELETE FROM " . TB_PREFIX . "raidlist WHERE lid IN ($fids)");
            }
            $this->database->query("DELETE FROM " . TB_PREFIX . "farmlist WHERE wref IN ($ids)");
        }

        $this->database->query("DELETE FROM " . TB_PREFIX . "users WHERE id = $uid");
        $this->database->query("DELETE FROM " . TB_PREFIX . "hero WHERE uid = $uid");
        $this->database->query("DELETE FROM " . TB_PREFIX . "mdata WHERE target = $uid OR owner = $uid");
        $this->database->query("DELETE FROM " . TB_PREFIX . "ndata WHERE uid = $uid");
        $this->database->query("DELETE FROM " . TB_PREFIX . "medal WHERE userid = $uid");
        $this->database->query("DELETE FROM " . TB_PREFIX . "gold_fin_log WHERE uid = $uid");
        $this->database->query("DELETE FROM " . TB_PREFIX . "links WHERE userid = $uid");
        $this->database->query("DELETE FROM " . TB_PREFIX . "online WHERE uid = $uid");
        $this->database->query("DELETE FROM " . TB_PREFIX . "chat WHERE id_user = $uid");
        $this->database->query("DELETE FROM " . TB_PREFIX . "login_log WHERE uid = $uid");
        $this->database->query("DELETE FROM " . TB_PREFIX . "banlist WHERE uid = $uid");
        $this->database->query("DELETE FROM " . TB_PREFIX . "deleting WHERE uid = $uid");
        $this->database->query("DELETE FROM " . TB_PREFIX . "password WHERE uid = $uid");
        $this->database->query("DELETE FROM " . TB_PREFIX . "illegal_log WHERE user = $uid");
        $this->database->query("DELETE FROM " . TB_PREFIX . "ali_invite WHERE uid = $uid");
        $this->database->query("DELETE FROM " . TB_PREFIX . "ali_permission WHERE uid = $uid");
        $this->database->query("DELETE FROM " . TB_PREFIX . "forum_post WHERE owner = $uid");
        $this->database->query("DELETE FROM " . TB_PREFIX . "forum_topic WHERE owner = $uid");
        $this->database->query("DELETE FROM " . TB_PREFIX . "artefacts WHERE owner = $uid");
        $this->database->query("DELETE FROM " . TB_PREFIX . "artefacts_chrono WHERE uid = $uid");

        $time = time();
        $logText = "Deleted player ID $uid ($rawUsername)";
        $logEsc = $this->database->escape($logText);
        $this->database->query(
            "INSERT INTO " . TB_PREFIX . "admin_log (`id`, `user`, `log`, `time`) " .
            "VALUES (0, '$admid', '$logEsc', $time)"
        );
        
        return true;
    }
}
