<?php

/**
 * F_array
 */
class F_array
{

    /**
     * 
     * @param array $listdb
     * @param array $sameKeys
     * @param array $sumKeys
     * @return array
     */
    public static function same_sum(array $listdb = [], array $sameKeys = [], array $sumKeys = []): array
    {
        $newdb = [];
        if (empty($listdb) === true || empty($sameKeys) === true || empty($sumKeys) === true) {
            return $newdb;
        }

        foreach ($listdb as $value) {
            $ckKey = "";
            foreach ($sameKeys as $sameKey) {
                $ckKey .= $value[$sameKey];
            }
            if (isset($newdb[$ckKey])) {
                foreach ($sumKeys as $sumKey) {
                    $newdb[$ckKey][$sumKey] += $value[$sumKey];
                }
            } else {
                $newdb[$ckKey] = $value;
            }
        }

        return $newdb;
    }

    /**
     * 
     * @param array $array
     * @param array $array_ck_key_value
     * @return array
     */
    public static function unique_multidim(array $array = [], array $array_ck_key_value = []): array
    {
        $temp_array = [];
        if (empty($array) === true || empty($array_ck_key_value) === true) {
            return $temp_array;
        }

        $i = 0;
        $key_array = [];
        foreach ($array as $val) {
            $ck_key_value = "";
            foreach ($array_ck_key_value as $ck_val) {
                $ck_key_value .= $val[$ck_val];
            }
            if (in_array($ck_key_value, $key_array) === false) {
                $key_array[$i] = $ck_key_value;
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }
}
