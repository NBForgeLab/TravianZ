<?php
declare(strict_types=1);



namespace App\Utils;

/**
 *
 * Date and Time related helpers.
 *
 * @author martinambrus
 *
 */
class DateTime
{
    public static function getTimeFormat(int $time): string
    {
        $min = 0;
        $hr = 0;
        $days = 0;
        while ($time >= 60): $time -= 60;
            $min += 1; endwhile;
        while ($min  >= 60): $min  -= 60;
            $hr  += 1; endwhile;
        while ($hr   >= 24): $hr   -= 24;
            $days += 1; endwhile;
        if ($min < 10) {
            $min = '0'.$min;
        }
        if ($time < 10) {
            $time = '0'.$time;
        }
        return $days .' day '.$hr.'h '.$min.'m '.$time.'s';
    }

}
