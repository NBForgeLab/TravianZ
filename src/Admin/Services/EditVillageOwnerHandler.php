<?php
namespace App\Admin\Services;

use App\Database\IDbConnection;

class EditVillageOwnerHandler
{
    private IDbConnection $database;

    public function __construct(IDbConnection $database)
    {
        $this->database = $database;
    }

    public function handle(int $did, int $newowner, int $adminId): array
    {
        $village = $this->database->getVillage($did);
        if (!$village) {
            return ['status' => 'novillage'];
        }

        $newUser = $this->database->getUserArray($newowner, 1);
        if (!$newUser) {
            return ['status' => 'nouser'];
        }

        $oldOwner = (int)$village['owner'];

        $this->database->query("UPDATE " . TB_PREFIX . "vdata SET owner = $newowner WHERE wref = $did");
        $this->database->query("UPDATE " . TB_PREFIX . "odata SET owner = $newowner WHERE conqured = $did");

        $time = time();
        $logText = "Changed owner for village <a href='admin.php?p=village&did=$did'>$did</a> from $oldOwner to <a href='admin.php?p=player&uid=$newowner'>$newowner</a>";
        $logEsc = $this->database->escape($logText);
        
        $this->database->query(
            "INSERT INTO " . TB_PREFIX . "admin_log (`id`, `user`, `log`, `time`) " .
            "VALUES (0, '$adminId', '$logEsc', $time)"
        );

        return ['status' => 'success', 'newowner' => $newowner];
    }
}
