<?php

namespace App\Admin\Services;

class GiveResBonusHandler
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function giveResBonus(int $gold, int $adminId, int $time)
    {
        if ($gold <= 0) {
            return false;
        }

        // Update
        $this->database->query("UPDATE " . TB_PREFIX . "users SET gold = gold + $gold WHERE id > 3");

        // Log admin
        $logText = "Gave $gold gold to all players";
        $logEsc = $this->database->escape($logText);

        $this->database->query(
            "INSERT INTO " . TB_PREFIX . "admin_log (`id`, `user`, `log`, `time`) " .
            "VALUES (0, '$adminId', '$logEsc', $time)"
        );

        return true;
    }
}
