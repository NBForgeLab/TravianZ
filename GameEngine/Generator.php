<?php

#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Filename       : Generator.php                                            ##
##  Type           : Generator System Backend                                  ##
## --------------------------------------------------------------------------- ##
##  Developed by   : Dzoki                                                   ##
##  Refactored by  : Shadow & Ferywir                                        ##
##  Thanks to      : ronix, InCube, Akakori, Elmar & Kirilloid                 ##
## --------------------------------------------------------------------------- ##
##  Contact        : cata7007@gmail.com                                        ##
##  Project        : TravianZ                                                  ##
##  URLs:          : https://travianz.org                                      ##
##  GitHub         : https://github.com/Shadowss/TravianZ                      ##
## --------------------------------------------------------------------------- ##
##  License        : TravianZ Project                                          ##
##  Copyright      : TravianZ (c) 2010-2026. All rights reserved.              ##
## --------------------------------------------------------------------------- ##
#################################################################################

class MyGenerator
{
    private $database;
    private $village;
    private $session;
    private $config; // For bid28, bid14

    public function __construct($database = null, $village = null, $session = null, $config = null)
    {
        $this->database = $database;
        $this->village = $village;
        $this->session = $session;
        $this->config = $config;
    }

    private function getDatabase()
    {
        return $this->database ?? ($GLOBALS['database'] ?? null);
    }

    private function getVillage()
    {
        return $this->village ?? ($GLOBALS['village'] ?? null);
    }

    private function getSession()
    {
        return $this->session ?? ($GLOBALS['session'] ?? null);
    }

    private function getConfig($key)
    {
        if (is_array($this->config) && array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }
        return $GLOBALS[$key] ?? [];
    }

    /**
     * Generate hashed random ID
     */
    public function generateRandID()
    {
        return md5($this->generateRandStr(16));
    }

    /**
     * Generate random string using secure random_int
     */
    public function generateRandStr($length)
    {
        $randstr = '';

        for ($i = 0; $i < $length; $i++) {
            $randnum = random_int(0, 61);

            if ($randnum < 10) {
                $randstr .= chr($randnum + 48);
            } elseif ($randnum < 36) {
                $randstr .= chr($randnum + 55);
            } else {
                $randstr .= chr($randnum + 61);
            }
        }

        return $randstr;
    }

    /**
     * Encode string to fixed-length md5 hash
     */
    public function encodeStr($str, $length)
    {
        $encode = md5($str);
        return substr($encode, 0, $length);
    }

    /**
     * Calculate travel/distance time between coordinates
     */
    public function procDistanceTime($coor, $thiscoor, $ref, $mode, $vid = 0)
    {
        $village = $this->getVillage();
        $vid = ($vid == 0 && $village !== null) ? $village->wid : $vid;

        $xdistance = abs($thiscoor['x'] - $coor['x']);
        if ($xdistance > WORLD_MAX) {
            $xdistance = (2 * WORLD_MAX + 1) - $xdistance;
        }

        $ydistance = abs($thiscoor['y'] - $coor['y']);
        if ($ydistance > WORLD_MAX) {
            $ydistance = (2 * WORLD_MAX + 1) - $ydistance;
        }

        $distance = sqrt(pow($xdistance, 2) + pow($ydistance, 2));

        if (!$mode) {
            if ($ref == 1) $speed = 16;
            elseif ($ref == 2) $speed = 12;
            elseif ($ref == 3) $speed = 24;
            elseif ($ref == 6) $speed = 20;
            elseif ($ref == 7) $speed = 16;
            elseif ($ref == 8) $speed = 14;
            elseif ($ref == 9) $speed = 18;
            elseif ($ref == 300) $speed = 5;
            else $speed = 1;
        } else {
            $speed = $ref;
            $tSquareLevel = $this->getDatabase()->getFieldLevelInVillage($vid, 14);
            $bid14 = $this->getConfig('bid14');

            if ($tSquareLevel > 0 && $distance >= TS_THRESHOLD && $speed > 0 && !empty($bid14[$tSquareLevel]['attri'])) {
                $boostedSpeed = $speed * ($bid14[$tSquareLevel]['attri'] / 100);
                $time = (TS_THRESHOLD / $speed) + (($distance - TS_THRESHOLD) / $boostedSpeed);

                return round($time * 3600 / INCREASE_SPEED);
            }
        }

        if ($speed > 0) {
            return round(($distance / $speed) * 3600 / INCREASE_SPEED);
        }

        return round($distance * 3600 / INCREASE_SPEED);
    }

    /**
     * Format seconds into H:i:s
     */
    public function getTimeFormat($time)
    {
        $time = max(0, (int) $time);
        $hr = intdiv($time, 3600);
        $min = intdiv($time % 3600, 60);
        $seconds = $time % 60;

        return sprintf('%d:%02d:%02d', $hr, $min, $seconds);
    }

    private function resolveUserTimeZone($tz)
    {
        if ($tz === null) {
            return new DateTimeZone(date_default_timezone_get());
        }
        $tz = (int) $tz;
        $named = [
            495 => 'Europe/Berlin',
            99  => 'Europe/London',
            492 => 'Europe/Istanbul',
            328 => 'Asia/Kolkata',
            345 => 'Asia/Bangkok',
            257 => 'America/New_York',
            189 => 'America/Chicago',
            474 => 'Pacific/Auckland',
        ];
        try {
            if (isset($named[$tz])) {
                return new DateTimeZone($named[$tz]);
            }
            if ($tz === 23) {
                $offset = 0;
            } elseif ($tz >= 0 && $tz <= 11) {
                $offset = $tz + 1;
            } elseif ($tz >= 12 && $tz <= 22) {
                $offset = $tz - 23;
            } else {
                $offset = 0;
            }
            $sign = $offset >= 0 ? '+' : '-';
            return new DateTimeZone(sprintf('%s%02d:00', $sign, abs($offset)));
        } catch (\Exception $e) {
            return new DateTimeZone(date_default_timezone_get());
        }
    }

    public function procMtime($time, $pref = 3)
    {
        $time = (int) $time;
        $session = $this->getSession();
        $tzPref = (isset($session) && isset($session->userinfo['timezone']))
            ? $session->userinfo['timezone'] : null;
        $tformat = (isset($session) && isset($session->userinfo['tformat']))
            ? (int) $session->userinfo['tformat'] : 0;

        $zone = $this->resolveUserTimeZone($tzPref);
        $dt = new DateTime('@' . $time);
        $dt->setTimezone($zone);
        $now = new DateTime('now', $zone);

        switch ($tformat) {
            case 1:  $dateFmt = "m/d/y"; $timeFmt = "h:i:s A"; break;
            case 2:  $dateFmt = "d/m/y"; $timeFmt = "h:i:s A"; break;
            case 3:  $dateFmt = "y/m/d"; $timeFmt = "H:i:s";   break;
            default: $dateFmt = "j.m.y"; $timeFmt = "H:i:s";   break;
        }

        if ($pref == 9) {
            return $dt->format("H:i:s");
        }

        $new = $dt->format($timeFmt);
        $yesterday = (clone $now)->modify('-1 day');

        if ($dt->format('Ymd') == $now->format('Ymd')) {
            $day = "today";
        } elseif ($dt->format('Ymd') == $yesterday->format('Ymd')) {
            $day = "yesterday";
        } else {
            $day = $dt->format($dateFmt);
        }
        return [$day, $new];
    }

    private function currentPlayerZone()
    {
        $session = $this->getSession();
        $tzPref = (isset($session) && isset($session->userinfo['timezone']))
            ? $session->userinfo['timezone'] : null;
        return $this->resolveUserTimeZone($tzPref);
    }

    public function userTimeZoneOffset()
    {
        return (new DateTime('now', $this->currentPlayerZone()))->getOffset();
    }

    public function getBaseID($x, $y)
    {
        return ((WORLD_MAX - $y) * (WORLD_MAX * 2 + 1)) + (WORLD_MAX + $x + 1);
    }

    public function getMapCheck($wref)
    {
        return substr(md5((string) $wref), 5, 2);
    }

    public function pageLoadTimeStart() {
        return $_SERVER["REQUEST_TIME_FLOAT"]?? microtime(true);
    }

    public function pageLoadTimeEnd() {
        return microtime(true);
    }
}
$generator = new MyGenerator();
