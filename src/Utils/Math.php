<?php
declare(strict_types=1);



namespace App\Utils;

/**
 *
 * Mathematics-related helpers.
 *
 * @author martinambrus
 *
 */
class Math
{
    public static function isInt(mixed $val): bool
    {
        return (is_numeric($val) && intval($val) === $val);
    }

    public static function isFloat(mixed $val): bool
    {
        return (is_numeric($val) && floatval($val) === $val);
    }

}
