<?php

namespace App\Admin\Services;

use App\Database\IDbConnection;

class UnbanService
{
    public function __construct(private IDbConnection $database)
    {
    }

    public function unbanByReason(string $reason, int $adminId): void
    {
        $reasonEsc = $this->database->escape($reason);
        $time = time();
        
        $this->database->query(
            "UPDATE " . TB_PREFIX . "banlist 
             SET active = 0, end = $time 
             WHERE reason = '$reasonEsc' AND active = 1"
        );
        
        $logText = "Mass unban for reason='$reasonEsc'";
        $logEsc = $this->database->escape($logText);
        
        $this->database->query(
            "INSERT INTO " . TB_PREFIX . "admin_log (`id`, `user`, `log`, `time`) " .
            "VALUES (0, '$adminId', '$logEsc', $time)"
        );
    }
}
