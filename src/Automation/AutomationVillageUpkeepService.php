<?php

namespace App\Automation;

use App\Database\IDbConnection;
use App\Automation\AutomationDataProvider;

class AutomationVillageUpkeepService {
    private $database;
    private $dataProvider;
    private $dependencyProvider;

    public function __construct(IDbConnection $database, AutomationDataProvider $dataProvider, $dependencyProvider) {
        $this->database = $database;
        $this->dataProvider = $dataProvider;
        $this->dependencyProvider = $dependencyProvider;
    }

    public function procResType($ref, $mode = 0) {
        return addslashes(empty($build = \Building::procResType($ref)) && !$mode ? RC_VILLAGE_CANT_BE : $build);
    }

    public function recountPop($vid, $use_cache = true){
        $database = $this->database;
        
        $vid = (int) $vid;
        $fdata = $database->getResourceLevel($vid, $use_cache);
        $popTot = 0;

        for ($i = 1; $i <= 40; $i++) {
            $lvl = $fdata["f".$i];
            $building = $fdata["f".$i."t"];
            if($building) $popTot += $this->buildingPOP($building, $lvl);
        }
        
        \Building::recountCP($database, $vid);
        $q = "UPDATE ".TB_PREFIX."vdata set pop = $popTot where wref = $vid";
        $database->query($q);
        $owner = $database->getVillageField($vid, "owner");

        if (defined('NEW_FUNCTIONS_MILESTONES') && NEW_FUNCTIONS_MILESTONES && $owner > 0 && $owner != 3) {
            $totalPop = (int) $database->getVSumField($owner, 'pop', false);
            if ($totalPop >= 1000) {
                $database->recordMilestoneIfFirst('population_1000', $owner, $vid);
            }
        }
        
        $this->dependencyProvider->procClimbers($owner);

        return $popTot;
    }

    private function buildingPOP($f, $lvl){
        $dataarray = $this->dataProvider->getBid($f);
        
        $popT = 0;

        for ($i = 0; $i <= $lvl; $i++) {
            $popT += ((isset($dataarray[$i]) && isset($dataarray[$i]['pop'])) ? $dataarray[$i]['pop'] : 0);
        }
        return $popT;
    }

    public function loyaltyRegeneration() {
        $database = $this->database;
        
        $array = $database->getProfileVillages(0, 6);
        if(!empty($array)) {
            foreach($array as $loyalty) {
                $value = 0;

                foreach (array(25, 26, 44) as $loyaltyBuilding) {
                    $lvl = $this->getTypeLevel($loyaltyBuilding, $loyalty['wref']);

                    if ($lvl >= 1) {
                        $value = $lvl;
                        break;
                    }
                }
                
                if($value > 0){
                    $newloyalty = min(100, $loyalty['loyalty'] + $value * (time() - $loyalty['lastupdate2']) / 3600);
                    $q = "UPDATE ".TB_PREFIX."vdata SET loyalty = $newloyalty, lastupdate2=".time()." WHERE wref = '".$loyalty['wref']."'";
                    $database->query($q);
                }            
            }
        }
        
        $q = "SELECT conqured, loyalty, lastupdated, wref FROM ".TB_PREFIX."odata WHERE loyalty < 100";
        $array = $database->query_return($q);
        if(!empty($array)) {
            foreach($array as $loyalty) {
                $value = $this->getTypeLevel(37, $loyalty['conqured']);   
                
                if($value > 0){
                    $newloyalty = min(100, $loyalty['loyalty'] + $value * (time() - $loyalty['lastupdated']) / 3600);
                    $q = "UPDATE ".TB_PREFIX."odata SET loyalty = $newloyalty, lastupdated=".time()." WHERE wref = '".$loyalty['wref']."'";
                    $database->query($q);
                }              
            }
        }
    }

    public function getTypeLevel($tid, $vid) {
        $database = $this->database;
        
        $keyholder = [];

        $resourcearray = $database->getResourceLevel($vid);
        foreach(array_keys($resourcearray, $tid) as $key) {
            if(strpos($key,'t')) {
                $key = preg_replace("/[^0-9]/", '', $key);
                array_push($keyholder, $key);
            }
        }
        
        $element = count($keyholder);
        $target = 0;
        
        if($element >= 2) {
            if($tid <= 4) {
                $temparray = [];
                for($i = 0; $i <= $element - 1; $i++) {
                    array_push($temparray,$resourcearray['f'.$keyholder[$i]]);
                }
                foreach ($temparray as $key => $val) {
                    if ($val == max($temparray)) $target = $key;                        
                }
            }
            else {
                $target = 0;
                for($i = 1; $i <= $element - 1; $i++) {
                    if($resourcearray['f'.$keyholder[$i]] > $resourcearray['f'.$keyholder[$target]]) {
                        $target = $i;
                    }
                }
            }
        }
        else if($element == 1) $target = 0;
        else return 0;

        if(!empty($keyholder[$target])) return $resourcearray['f'.$keyholder[$target]];
        else return 0;
    }
    
    private function pruneResourceMinimums($table) {
        $database = $this->database;

        $database->query("UPDATE
                  ".TB_PREFIX.$table."
              SET
                  wood = IF(wood < 0, 0, wood),
                  clay = IF(clay < 0, 0, clay),
                  iron = IF(iron < 0, 0, iron),
                  crop = IF(crop < 0, 0, crop),
                  maxstore = IF(maxstore < ".STORAGE_BASE.", ".STORAGE_BASE.", maxstore),
                  maxcrop = IF(maxcrop < ".STORAGE_BASE.", ".STORAGE_BASE.", maxcrop)
              WHERE
                  maxstore < ".STORAGE_BASE." OR
                  maxcrop < ".STORAGE_BASE." OR
                  wood < 0 OR
                  clay < 0 OR
                  iron < 0 OR
                  crop < 0");
    }

    public function pruneOResource() {
        if(!ALLOW_BURST) {
            $this->pruneResourceMinimums('odata');
        }
    }
    public function pruneResource() {
        $database = $this->database;

        if(!ALLOW_BURST) {
            $this->pruneResourceMinimums('vdata');

            $database->query("UPDATE
                      ".TB_PREFIX."vdata
                  SET
                      wood = IF(wood > maxstore, maxstore, wood),
                      clay = IF(clay > maxstore, maxstore, clay),
                      iron = IF(iron > maxstore, maxstore, iron),
                      crop = IF(crop > maxcrop, maxcrop, crop)
                  WHERE
                      wood > maxstore OR
                      clay > maxstore OR
                      iron > maxstore OR
                      crop > maxcrop");
        }
    }

    public function culturePoints() {
        $database = $this->database;

        $this->applyHeroCulturePoints();

        $database->updateVSumField('cp');
    }

    private function applyHeroCulturePoints() {
        $database = $this->database;
        $heroItemCatalog = $this->dataProvider->getHeroItemCatalog();

        if (!class_exists('HeroBattleBonus') || !HeroBattleBonus::enabled()) {
            return;
        }

        if (empty($heroItemCatalog) || !is_array($heroItemCatalog)) {
            return;
        }

        $heroItems = new \HeroItems();
        $cpValues  = array();

        foreach ($heroItemCatalog as $itemid => $def) {
            if (!isset($def['bonus']) || !is_array($def['bonus']) || !isset($def['bonus'][HB_CP])) {
                continue;
            }

            $value = (int) $heroItems->scaledBonusValue($def, (int) $def['bonus'][HB_CP]);

            if ($value > 0) {
                $cpValues[(int) $itemid] = $value;
            }
        }

        if (empty($cpValues)) {
            return;
        }

        $durDay = 86400 / SPEED;

        if ($durDay < 3600) {
            $durDay = 3600;
        }

        $case = '';

        foreach ($cpValues as $itemid => $value) {
            $case .= ' WHEN ' . (int) $itemid . ' THEN ' . (int) $value;
        }

        $ids = implode(',', array_map('intval', array_keys($cpValues)));

        $q = "UPDATE " . TB_PREFIX . "users AS u
              INNER JOIN " . TB_PREFIX . "hero_items AS hi
                      ON hi.uid = u.id AND hi.equipped = 1 AND hi.itemid IN (" . $ids . ")
              INNER JOIN " . TB_PREFIX . "hero AS h
                      ON h.uid = u.id AND h.dead = 0
              SET u.cp = u.cp + ((CASE hi.itemid" . $case . " END) * (UNIX_TIMESTAMP() - u.lastupdate) / " . $durDay . ")
              WHERE u.lastupdate < (UNIX_TIMESTAMP() - 600)";

        mysqli_query($database->dblink, $q);
    }

    public function updateStorageCapacity($type, $level, $villageData) {
        $bidType = $this->dataProvider->getBid($type);

        $fieldDbName = (in_array($type, [10, 38]) ? 'maxstore' : 'maxcrop');
        $max = $villageData[$fieldDbName];

        if($level == 1 && $max == STORAGE_BASE) $max = STORAGE_BASE;

        if ($level != 1) $max -= $bidType[$level - 1]['attri'] * STORAGE_MULTIPLIER;

        $max += $bidType[$level]['attri'] * STORAGE_MULTIPLIER;

        return [$fieldDbName, $max];
    }

    public function getPop($tid, $level) {
        $dataarray = $this->dataProvider->getBid($tid);
        
        $pop = $dataarray[($level + 1)]['pop'];
        $cp = $dataarray[($level + 1)]['cp'];
        return [$pop, $cp];
    }

    public function updateORes($bountywid) {
        $database = $this->database;
        
        $oasisInfoArray = $database->getOasisV($bountywid);
        $timepast = time() - $oasisInfoArray['lastupdated'];
        $nwood = (OASIS_WOOD_PRODUCTION / 3600) * $timepast;
        $nclay = (OASIS_CLAY_PRODUCTION / 3600) * $timepast;
        $niron = (OASIS_IRON_PRODUCTION / 3600) * $timepast;
        $ncrop = (OASIS_CROP_PRODUCTION / 3600) * $timepast;
        $database->modifyOasisResource($bountywid, $nwood, $nclay, $niron, $ncrop, 1);
        $database->updateOasis($bountywid);
    }
    
    public function updateRes($bountywid) {
        $database = $this->database;
        $technology = $this->dataProvider->getTechnology();
        
        $villageInfoArray = $database->getVillage($bountywid);

        if (!is_array($villageInfoArray)) return;
        
        $resArray = $database->getResourceLevel($bountywid, false);
        $oasisArray = $database->getOasis($bountywid);
        $numberOfOasis = $this->dependencyProvider->bountysortOasis($oasisArray);
        
        $villagePopulation = !$villageInfoArray['natar'] ? $villageInfoArray['pop'] : round($villageInfoArray['pop'] / 2);
        
        $upkeep = $technology->getUpkeep($this->dependencyProvider->getAllUnits($bountywid), 0, $bountywid);   	
 
        $timepast = time() - $villageInfoArray['lastupdate'];
        $nwood = ($this->dependencyProvider->bountyGetResourceProd($resArray, $numberOfOasis, 1) / 3600) * $timepast;
        $nclay = ($this->dependencyProvider->bountyGetResourceProd($resArray, $numberOfOasis, 2) / 3600) * $timepast;
        $niron = ($this->dependencyProvider->bountyGetResourceProd($resArray, $numberOfOasis, 3) / 3600) * $timepast;
        $ncrop = (($this->dependencyProvider->bountyGetResourceProd($resArray, $numberOfOasis, 4) - $villagePopulation - $upkeep) / 3600) * $timepast;
        $database->modifyResource($bountywid, $nwood, $nclay, $niron, $ncrop, 1);
        $database->updateVillage($bountywid);
    }

    public function updateStore() {
        $database = $this->database;
        
        for ($attempt = 1; $attempt <= 3; $attempt++) {
            try {
                $result = mysqli_query($database->dblink, 'SELECT * FROM `' . TB_PREFIX . 'fdata`');

                $pending = 0;
                mysqli_begin_transaction($database->dblink);
                while ($row = mysqli_fetch_assoc($result))
                {
                    $ress = $crop = 0;
                    for ($i = 19; $i < 40; ++$i)
                    {
                        $bid10 = $this->dataProvider->getBid(10);
                        $bid38 = $this->dataProvider->getBid(38);
                        $bid11 = $this->dataProvider->getBid(11);
                        $bid39 = $this->dataProvider->getBid(39);

                        //Warehouse
                        if ($row['f' . $i . 't'] == 10)
                        {
                            $ress += ((isset($bid10[$row['f' . $i]]) && isset($bid10[$row['f' . $i]]['attri'])) ? $bid10[$row['f' . $i]]['attri'] * STORAGE_MULTIPLIER : 0);
                        }

                        //Great warehouse
                        if ($row['f' . $i . 't'] == 38)
                        {
                            $ress += ((isset($bid38[$row['f' . $i]]) && isset($bid38[$row['f' . $i]]['attri'])) ? $bid38[$row['f' . $i]]['attri'] * STORAGE_MULTIPLIER : 0);
                        }
                
                        //Granary
                        if ($row['f' . $i . 't'] == 11)
                        {
                            $crop += ((isset($bid11[$row['f' . $i]]) && isset($bid11[$row['f' . $i]]['attri'])) ? $bid11[$row['f' . $i]]['attri'] * STORAGE_MULTIPLIER : 0);
                        }
              
                        //Great granary
                        if ($row['f' . $i . 't'] == 39)
                        {
                            $crop += ((isset($bid39[$row['f' . $i]]) && isset($bid39[$row['f' . $i]]['attri'])) ? $bid39[$row['f' . $i]]['attri'] * STORAGE_MULTIPLIER : 0);
                        }
                    }

                    if ($ress == 0 && $crop == 0) continue;

                    if ($ress < STORAGE_BASE) $ress = STORAGE_BASE;
                    if ($crop < STORAGE_BASE) $crop = STORAGE_BASE;

                    mysqli_query($database->dblink,'UPDATE `' . TB_PREFIX . 'vdata` SET `maxstore` = ' . (int) $ress . ', `maxcrop` = ' . (int) $crop . ' WHERE `wref` = ' . (int) $row['vref']);

                    if (++$pending >= 200) {
                        mysqli_commit($database->dblink);
                        mysqli_begin_transaction($database->dblink);
                        $pending = 0;
                    }
                }
                mysqli_commit($database->dblink);
                return;
            } catch (mysqli_sql_exception $e) {
                if (!in_array((int) $e->getCode(), [1213, 1205], true)) {
                    throw $e; 
                }
                try { mysqli_rollback($database->dblink); } catch (mysqli_sql_exception $ignored) {}
                if ($attempt === 3) {
                    error_log('AutomationVillageUpkeepService::updateStore: deadlock persistent dupa 3 incercari, amanat pentru tick-ul urmator');
                    return;
                }
                usleep(150000 * $attempt);
            }
        }
    }

    public function regenerateOasisTroops() {
        $database = $this->database;
        
        $timeFinal = time() - NATURE_REGTIME;
        $q = "SELECT wref FROM " . TB_PREFIX . "odata where conqured = 0 and lastupdated2 < $timeFinal";
        $array = $database->query_return($q);
        if (count($array)) {
            $ids = [];
            foreach($array as $oasis) $ids[] = $oasis['wref'];
            $database->regenerateOasisUnits($ids, true);
        }
    }
}
