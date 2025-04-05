<?php

/**
 * F_algorithm
 */
class F_algorithm
{

    /**
     * 
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float
     */
    public static function cal_distance(float $lat1 = 0, float $lon1 = 0, float $lat2 = 0, float $lon2 = 0): float
    {
        $the = $lon1 - $lon2;
        $cal_form = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($the));
        $dist = acos($cal_form);
        $miles = rad2deg($dist) * 60 * 1.1515;
        return ($miles * 1.609344);
    }

    /**
     * 
     * @param int $n
     * @return array
     */
    public static function fibonacci(int $n = 0): array
    {
        $arrResult = [];
        if ($n < 2) {
            $arrResult[] = $n;
            return $arrResult;
        }

        $num1 = 0;
        $num2 = 1;
        for ($counter = 0; $counter < $n; $counter++) {
            $arrResult[] = $num1;
            $num3 = $num2 + $num1;
            $num1 = $num2;
            $num2 = $num3;
        }

        return $arrResult;
    }

    /**
     * 
     * @param string $value
     * @return bool
     */
    public static function validateOn(string $value = ""): bool
    {
        if (empty($value) === true) {
            return false;
        }
        $length = strlen($value);
        if ($length > 9 || is_numeric($value) === false) {
            return false;
        }
        if ($length < 9) {
            $value = str_pad($value, 9, "0", STR_PAD_LEFT);
        }

        $result = 0;
        $products = range(9, 2);
        $listdb = str_split($value);
        $last = end($listdb);
        array_pop($listdb);
        foreach ($listdb as $i => $char) {
            $result += (int) $char * $products[$i];
        }

        if ($result % 11 === $last + 5) {
            return true;
        }
        return false;
    }

    /**
     * 
     * @param string $value
     * @return bool
     */
    public static function validateBsn(string $value = ""): bool
    {
        if (empty($value) === true) {
            return false;
        }

        $length = strlen($value);
        if ($length > 9 || is_numeric($value) === false) {
            return false;
        }
        if ($length < 9) {
            $value = str_pad($value, 9, "0", STR_PAD_LEFT);
        }

        $result = 0;
        $products = range(9, 2);
        $products[] = -1;

        $listdb = str_split($value);
        foreach ($listdb as $i => $char) {
            $result += (int) $char * $products[$i];
        }

        if ($result % 11 === 0) {
            return true;
        }
        return false;
    }

    /**
     * 
     * @param string $value
     * @return bool
     */
    public static function validateBtw(string $value = ""): bool
    {
        if (empty($value) === true) {
            return false;
        }

        $length = strlen($value);
        $result = (bool) preg_match('/^NL[0-9]{9}B[0-9]{2}$/', strtoupper($value));

        if ($length > 14 || $result === false) {
            return false;
        }

        $bsn = substr($value, 2, -3);
        return self::validateBsn($bsn);
    }

    /**
     * 
     * @param string $value
     * @return bool
     */
    public static function validateIban(string $value = ""): bool
    {
        if (empty($value) === true) {
            return false;
        }

        $testiban = strtoupper($value);
        $testiban = str_replace(' ', '', $testiban);
        if (strlen($testiban) !== 18) {
            return false;
        }

        $iban_replace_chars = range('A', 'Z');
        $iban_replace_values = [];
        foreach (range(10, 35) as $intvalue) {
            $iban_replace_values[] = (string) $intvalue;
        }

        $testiban = substr($testiban, 4) . substr($testiban, 0, 4);
        $testiban = str_replace(
            $iban_replace_chars,
            $iban_replace_values,
            $testiban
        );
        $head = (int) substr($testiban, 0, 9);
        $tail = substr($testiban, 9);
        $mod = $head % 97;
        foreach (str_split($tail, 7) as $chunk) {
            $digit = (int) ((string) $mod . $chunk);
            $mod = $digit % 97;
        }
        return $mod === 1;
    }
}
