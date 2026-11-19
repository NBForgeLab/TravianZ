<?php

namespace App\Admin\Services;

use App\Database\IDbConnection;

class ResetPlusService
{
    public function __construct(private IDbConnection $database)
    {
    }

    public function resetAllPlus(int $adminId): void
    {
        $this->database->query("UPDATE " . TB_PREFIX . "users SET plus = 0 WHERE id > 0");
        
        $time = time();
        $logText = "Reset Plus to 0 for all users";
        $logEsc = $this->database->escape($logText);
        
        $this->database->query(
            "INSERT INTO " . TB_PREFIX . "admin_log (`id`, `user`, `log`, `time`) " .
            "VALUES (0, '$adminId', '$logEsc', $time)"
        );
    }
}
