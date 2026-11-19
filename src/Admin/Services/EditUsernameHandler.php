<?php
namespace App\Admin\Services;

use App\Database\IDbConnection;

class EditUsernameHandler
{
    private IDbConnection $database;

    public function __construct(IDbConnection $database)
    {
        $this->database = $database;
    }

    public function handle(int $uid, string $username, int $adminId): array
    {
        // 1. Validate username
        $usernameSpecial = defined('USRNM_SPECIAL') ? USRNM_SPECIAL : false;
        $minLen = defined('USRNM_MIN_LENGTH') ? USRNM_MIN_LENGTH : 3;
        $maxLen = defined('USRNM_MAX_LENGTH') ? USRNM_MAX_LENGTH : 15;
        
        $charsOk = $usernameSpecial
            ? (bool)preg_match('/^[A-Za-z0-9._-]+(?: [A-Za-z0-9._-]+)*$/D', $username)
            : !preg_match('/[^0-9A-Za-z]/', $username);

        if (strlen($username) < $minLen || strlen($username) > $maxLen || !$charsOk) {
            return ['status' => 'invalid'];
        }

        // 2. Check for duplicate
        $result = $this->database->query("SELECT id FROM " . TB_PREFIX . "users WHERE username = '" . $this->database->escape($username) . "' AND id != $uid LIMIT 1");
        $hasDuplicate = ($result instanceof \mysqli_result)
            ? ($this->database->num_rows($result) > 0 || $result->num_rows > 0)
            : (is_object($result) && ($result->num_rows ?? 0) > 0);
        if ($hasDuplicate) {
            return ['status' => 'taken'];
        }

        // 3. Update
        $usernameEsc = $this->database->escape($username);
        $this->database->query("UPDATE " . TB_PREFIX . "users SET username = '$usernameEsc' WHERE id = $uid");

        // 4. Log
        $time = time();
        $logText = "Changed username for user $uid to '$username'";
        $logEsc = $this->database->escape($logText);
        $this->database->query(
            "INSERT INTO " . TB_PREFIX . "admin_log (`id`, `user`, `log`, `time`) " .
            "VALUES (0, '$adminId', '$logEsc', $time)"
        );

        return ['status' => 'success'];
    }
}
