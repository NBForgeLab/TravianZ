<?php
namespace App\Admin\Services;

use App\Database\IDbConnection;

class EditWeekHandler
{
    private IDbConnection $database;

    public function __construct(IDbConnection $database)
    {
        $this->database = $database;
    }

    public function handle(int $id, int $ap, int $dp, int $rr, int $adminId): bool
    {
        $this->database->query("UPDATE " . TB_PREFIX . "users SET ap = $ap, dp = $dp, RR = $rr WHERE id = $id");

        $time = time();
        $logText = "Changed weekly stats for user <a href='admin.php?p=player&uid=$id'>$id</a> (ap=$ap, dp=$dp, RR=$rr)";
        $logEsc = $this->database->escape($logText);
        
        $this->database->query(
            "INSERT INTO " . TB_PREFIX . "admin_log (`id`, `user`, `log`, `time`) " .
            "VALUES (0, '$adminId', '$logEsc', $time)"
        );

        return true;
    }
}
