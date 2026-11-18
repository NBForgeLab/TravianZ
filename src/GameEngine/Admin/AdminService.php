<?php
namespace App\GameEngine\Admin;

class AdminService {
    private $database;
    private $connection;
    private $adminId;

    public function __construct($database, $adminId) {
        $this->database = $database;
        $this->connection = $database->return_link();
        $this->adminId = $adminId;
    }

    public function punish($uid, $punish, $delTroop, $cleanWare) {
        $villages = $this->database->getProfileVillages($uid);
        $user = $this->database->getUserArray($uid, 1);
        $logPunishment = '';

        foreach ($villages as $village) {
            $vid = (int)$village['wref'];

            if (!empty($punish)) {
                $p = (int)$punish;
                $logPunishment = "<b>-". $p ."%</b> population";
                $popOld = (int)$village['pop'];
                $proc = 100 - $p;
                $pop = floor(($popOld / 100) * $proc);
                if ($pop < 2) { $pop = 2; }
                $this->punishBuilding($vid, $proc, $pop);
            }

            if (!empty($delTroop)) {
                $logPunishment = "<b>troops removal</b>";
                $tribe = (int)$user['tribe'];
                $unitStart = ($tribe >= 1 && $tribe <= 9) ? (($tribe - 1) * 10 + 1) : 1;
                $this->delUnits($vid, $unitStart);
            }

            if (!empty($cleanWare)) {
                $logPunishment = "<b>emptying warehouses</b>";
                $time = time();
                $q = "UPDATE ". TB_PREFIX ."vdata SET `wood`='0', `clay`='0', `iron`='0', `crop`='0', `lastupdate`='$time' WHERE wref=$vid";
                mysqli_query($this->connection, $q);
            }
        }

        mysqli_query($this->connection, "INSERT INTO ". TB_PREFIX ."admin_log VALUES (0,". (int)$this->adminId .",'Punished user: <a href=\'admin.php?p=player&uid=". (int)$uid ."\'>". (int)$uid ."</a> with ". $logPunishment ."',". time() .")");
    }

    private function punishBuilding($vid, $proc, $pop) {
        mysqli_query($this->connection, "UPDATE ". TB_PREFIX ."vdata SET pop=". (int)$pop ." WHERE wref=" . (int)$vid);
        
        $fdata = $this->database->getResourceLevel($vid);
        for ($i = 1; $i <= 40; $i++) {
            if ($fdata['f'.$i] > 1) {
                $zm = ($fdata['f'.$i] / 100) * $proc;
                $zm = ($zm < 1) ? 1 : floor($zm);
                mysqli_query($this->connection, "UPDATE ". TB_PREFIX ."fdata SET `f$i`='$zm' WHERE `vref`=" . (int)$vid);
            }
        }
    }

    private function delUnits($vid, $unitStart) {
        $sets = [];
        for ($i = $unitStart; $i <= $unitStart + 9; $i++) {
            $sets[] = "`u$i`='0'";
        }
        $q = "UPDATE ". TB_PREFIX ."units SET ". implode(', ', $sets) ." WHERE `vref`=" . (int)$vid;
        mysqli_query($this->connection, $q);
    }
}
