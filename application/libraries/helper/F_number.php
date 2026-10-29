<?php

/**
 * F_number
 */
class F_number
{

    public static function money($val = 0, int $pos = 2): string
    {
        return number_format($val, $pos, ',', '.');
    }

    public static function random_float(int $min, int $max, int $decimals = 1): float
    {
        $scale = pow(10, $decimals);
        return mt_rand($min * $scale, $max * $scale) / $scale;
    }

    public static function filesize(int $bytes = 0, int $decimals = 2)
    {
        $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }

    public static function moneyVal(string $amount = "", bool $hasComma = true)
    {
        if (empty($amount)) {
            return 0;
        }
        $amount_1 = str_replace(".", "", $amount);
        if ($hasComma === true) {
            $amount_1 = str_replace(",", ".", $amount_1);
        }
        return floatval($amount_1);
    }
}
