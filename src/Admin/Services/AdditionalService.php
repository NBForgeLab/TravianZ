<?php

namespace App\Admin\Services;

class AdditionalService
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function updateAdditional(
        int $id,
        int $access,
        int $newGold,
        int $sit1,
        int $sit2,
        int $protect,
        int $cp,
        int $ap,
        int $dp,
        int $rr,
        int $apall,
        int $dpall,
        int $vac_mode,
        int $adminUid,
        string $adminName,
        string $playerName
    ) {
        $oldGold = (int)$this->database->getUserField($id, 'gold', 0, false);
        $diffGold = $newGold - $oldGold;

        // --- UPDATE USER ---
        $this->database->query("
            UPDATE ".TB_PREFIX."users SET
                access = $access,
                gold = $newGold,
                sit1 = $sit1,
                sit2 = $sit2,
                protect = $protect,
                cp = $cp,
                ap = $ap,
                dp = $dp,
                RR = $rr,
                apall = $apall,
                dpall = $dpall,
                vac_mode = $vac_mode
            WHERE id = $id
        ");
        $this->database->clearQueryCache('getUser');

        // --- LOG GOLD dacă s-a modificat ---
        if ($diffGold !== 0) {
            $vill = $this->database->getVillagesID($id);
            $wid = $vill[0] ?? 0;
            $action = $diffGold > 0 ? 'Admin added Gold' : 'Admin removed Gold';
            $details = 'Admin adjustment by ' . $adminName;
            $now = time();

            $action_esc = $this->database->escape($action);
            $details_esc = $this->database->escape($details);

            $this->database->query("
                INSERT INTO ".TB_PREFIX."gold_fin_log
                (uid, wid, action, gold, time, details)
                VALUES ($id, $wid, '$action_esc', $diffGold, $now, '$details_esc')
            ");
        }

        // --- LOG ADMIN ---
        $protectDays = (int)round(($protect - time()) / 86400);

        $logParts = [];
        $logParts[] = "Gold: $oldGold → $newGold". ($diffGold != 0 ? " ($diffGold)" : "");
        $logParts[] = "VacMode: $vac_mode";
        $logParts[] = "Access: $access";
        $logParts[] = "Protect: {$protectDays}d";
        $logParts[] = "Sitters: $sit1/$sit2";

        $logText = "[$adminName] edited Additional for [$playerName] (UID:$id) - ". implode(' | ', $logParts);
        $logText = $this->database->escape($logText);

        $now = time();
        $this->database->query("
            INSERT INTO ".TB_PREFIX."admin_log
            (`user`, `log`, `time`)
            VALUES ('$adminUid', '$logText', $now)
        ");
    }
}
