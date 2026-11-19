<?php
namespace App\Admin\Services;

use App\Database\IDbConnection;

class GivePlusHandler
{
    private IDbConnection $database;

    public function __construct(IDbConnection $database)
    {
        $this->database = $database;
    }

    public function handle(int $days, int $adminId): bool
    {
        $plusdur = $days * 86400;
        $time = time();

        $this->database->query(
            "UPDATE " . TB_PREFIX . "users 
             SET plus = IF(plus < $time, $time + $plusdur, plus + $plusdur) 
             WHERE id > 3"
        );

        $logText = "Gave $days days Plus to all players";
        $logEsc = $this->database->escape($logText);

        $this->database->query(
            "INSERT INTO " . TB_PREFIX . "admin_log (`id`, `user`, `log`, `time`) " .
            "VALUES (0, '$adminId', '$logEsc', $time)"
        );

        return true;
    }
}
