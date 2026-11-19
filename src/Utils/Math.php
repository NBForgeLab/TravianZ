<?php
#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Project:       TravianZ                                                    ##
##  Filename       Math.php                                                    ##
##  Developed by:  martinambrus                                                ##
##  License:       TravianZ Project                                            ##
##  Copyright:     TravianZ (c) 2010-2017. All rights reserved.                ##
##  URLs:          https://travian.martinambrus.com                		       ##
##  Source code:   https://github.com/Shadowss/TravianZ		                   ##
##                                                                             ##
#################################################################################

namespace App\Utils;

/**
 *
 * Mathematics-related helpers.
 *
 * @author martinambrus
 *
 */
class Math {

    public static function isInt($val) {
        if (is_bool($val) || is_float($val) || !is_scalar($val) || !is_numeric($val)) {
            return false;
        }
        $val = (string)$val;
        return (string)(int)$val === $val;
    }

    public static function isFloat($val) {
        if (is_bool($val) || !is_scalar($val) || !is_numeric($val)) {
            return false;
        }
        $f = (float)$val;
        if (!is_finite($f)) {
            return false;
        }
        if (is_float($val)) {
            return true;
        }
        return is_string($val) && strpos($val, '.') !== false && (string)$f === trim($val);
    }

}