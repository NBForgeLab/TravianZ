<?php

namespace App\Admin\Services;

use App\Database\IDbConnection;

class ResetPlusBonusService
{
    public function __construct(private IDbConnection $database)
    {
    }

    public function resetAllPlusBonus(int $adminId): void
    {
        $this->database->query("UPDATE " . TB_PREFIX . "users SET b1 = 0, b2 = 0, b3 = 0, b4 = 0 WHERE id > 0");
        
        $time = time();
        $logText = "Reset resource bonuses (b1-b4) to 0 for all users";
        $logEsc = $this->database->escape($logText);
        
        $this->database->query(
            "INSERT INTO " . TB_PREFIX . "admin_log (`id`, `user`, `log`, `time`) " .
            "VALUES (0, '$adminId', '$logEsc', $time)"
        );
    }
}
