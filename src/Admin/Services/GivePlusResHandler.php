<?php

namespace App\Admin\Services;

class GivePlusResHandler
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function givePlusRes(array $postData, int $adminId, int $time)
    {
        $wood = (int)($postData['wood'] ?? 0) * 86400;
        $clay = (int)($postData['clay'] ?? 0) * 86400;
        $iron = (int)($postData['iron'] ?? 0) * 86400;
        $crop = (int)($postData['crop'] ?? 0) * 86400;

        if ($wood + $clay + $iron + $crop == 0) {
            return false;
        }

        // Update în masă
        if ($wood > 0) {
            $this->database->query("UPDATE " . TB_PREFIX . "users SET b1 = IF(b1 < $time, $time + $wood, b1 + $wood) WHERE id > 3");
        }
        if ($clay > 0) {
            $this->database->query("UPDATE " . TB_PREFIX . "users SET b2 = IF(b2 < $time, $time + $clay, b2 + $clay) WHERE id > 3");
        }
        if ($iron > 0) {
            $this->database->query("UPDATE " . TB_PREFIX . "users SET b3 = IF(b3 < $time, $time + $iron, b3 + $iron) WHERE id > 3");
        }
        if ($crop > 0) {
            $this->database->query("UPDATE " . TB_PREFIX . "users SET b4 = IF(b4 < $time, $time + $crop, b4 + $crop) WHERE id > 3");
        }

        // Log admin
        $logText = "Gave res bonuses to all: wood=" . ($postData['wood'] ?? 0) . "d, clay=" . ($postData['clay'] ?? 0) . "d, iron=" . ($postData['iron'] ?? 0) . "d, crop=" . ($postData['crop'] ?? 0) . "d";
        $logEsc = $this->database->escape($logText);

        $this->database->query(
            "INSERT INTO " . TB_PREFIX . "admin_log (`id`, `user`, `log`, `time`) " .
            "VALUES (0, '$adminId', '$logEsc', $time)"
        );

        return true;
    }
}
