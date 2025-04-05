<?php

trait JsonSearch
{

    protected function queryMinMax(string $inputnamemin = '', string $inputnamemax = '', string $path = '', string $table = 'page_result', string $field = 'json_question_result'): array
    {
        $query = [];
        $valueMax = (float)CIInput()->post_get($inputnamemax) ??  0;
        $valueMin = (float)CIInput()->post_get($inputnamemin) ??  0;

        $jsonDoc = $this->db->dbprefix($table) . '.' . $field;

        if (empty($valueMax) === false && empty($valueMin) === true) {
            $query =
                [
                    'JSON_VALUE(' . $jsonDoc . ',\'$.' . $path . '\') <=' => $valueMax
                ];
        }

        if (empty($valueMax) === true && empty($valueMin) === false) {
            $query =
                [
                    'JSON_VALUE(' . $jsonDoc . ',\'$.' . $path . '\') >=' => $valueMin
                ];
        }

        if (empty($valueMax) === false && empty($valueMin) === false) {

            $query =
                [
                    'JSON_VALUE(' . $jsonDoc . ',\'$.' . $path . '\') >=' => $valueMin,
                    'JSON_VALUE(' . $jsonDoc . ',\'$.' . $path . '\') <=' => $valueMax
                ];
        }

        return $query;
    }


    protected function queryDateStartEnd(string $inputnamestart = '', string $inputnameend = '', string $path = '', string $table = 'page_result', string $field = 'json_question_result', string $dateformat = '%d-%m-%Y'): array
    {
        $query = [];
        $valueEnd = CIInput()->post_get($inputnameend) ??  "";
        $valueStart = CIInput()->post_get($inputnamestart) ??  "";
        $jsonDoc = $this->db->dbprefix($table) . '.' . $field;
        if (empty($valueStart) === false && empty($valueEnd) === true) {
            $query =
                [
                    'STR_TO_DATE(JSON_VALUE(' . $jsonDoc . ',\'$.' . $path . '\'),"' . $dateformat . '") >=' => date_format(date_create(trim($valueStart)), 'Y-m-d'),
                ];
        }

        if (empty($valueStart) === true && empty($valueEnd) === false) {
            $query =
                [
                    'STR_TO_DATE(JSON_VALUE(' . $jsonDoc . ',\'$.' . $path . '\'),"' . $dateformat . '") <=' => date_format(date_create(trim($valueEnd)), 'Y-m-d')
                ];
        }

        if (empty($valueStart) === false && empty($valueEnd) === false) {
            $query =
                [
                    'STR_TO_DATE(JSON_VALUE(' . $jsonDoc . ',\'$.' . $path . '\'),"' . $dateformat . '") >=' => date_format(date_create(trim($valueStart)), 'Y-m-d'),
                    'STR_TO_DATE(JSON_VALUE(' . $jsonDoc . ',\'$.' . $path . '\'),"' . $dateformat . '") <=' => date_format(date_create(trim($valueEnd)), 'Y-m-d')
                ];
        }

        return $query;
    }

    protected  function queryFindInSet(string $inputname = '', string $path = '', string $table = 'page_result', string $field = 'json_question_result'): array
    {
        $value = CIInput()->post_get($inputname) ??  [];
        $query = [];
        $jsonDoc = $this->db->dbprefix($table) . '.' . $field;

        if (empty($value) === false) {
            $replaceJsonExtract = 'replace(replace(replace(replace(JSON_EXTRACT(' . $jsonDoc . ',\'$.' . $path . '\'), \' \', \'\'), \'\"\', \'\'), \'[\', \'\'), \']\', \'\')';
            $elements = [];
            foreach ($value as $element) {
                if (is_numeric($element)) {
                    $elements[] = 'FIND_IN_SET (' . $element . ',' . $replaceJsonExtract . ')';
                } else {
                    $elements[] = 'FIND_IN_SET (' . '"' . $element . '"' . ',' . $replaceJsonExtract . ')';
                }
            }
            $operator = implode(' OR ', $elements);
            $query = [$operator => 'value_is_null'];
        }

        return $query;
    }

    protected function queryBool(string $inputname = '', string $path = '', string $table = 'page_result', string $field = 'json_question_result'): array
    {
        $value = CIInput()->post_get($inputname) ?? "";
        $query = [];
        $jsonDoc = $this->db->dbprefix($table) . '.' . $field;
        if ($value != "") {
            $bool_value = intval($value);
            $query = ['JSON_VALUE(' . $jsonDoc . ',\'$.' . $path . '\')' => $bool_value];
        }

        return $query;
    }


    protected function queryDateRange(string $inputname = '', string $path = '', string $table = 'page_result', string $field = 'json_question_result', string $dateformat = '%d-%m-%Y'): array
    {
        $value = CIInput()->post_get($inputname) ?? "";
        $query = [];
        $jsonDoc = $this->db->dbprefix($table) . '.' . $field;
        if (empty($value) === false) {
            $arr_range = explode("t/m", $value);
            $query =
                [
                    'STR_TO_DATE(JSON_VALUE(' . $jsonDoc . ',\'$.' . $path . '\'),"' . $dateformat . '") >=' => date_format(date_create(trim($arr_range[0])), 'Y-m-d'),
                    'STR_TO_DATE(JSON_VALUE(' . $jsonDoc . ',\'$.' . $path . '\'),"' . $dateformat . '") <=' => date_format(date_create(trim($arr_range[1])), 'Y-m-d')
                ];
        }
        return $query;
    }


    protected  function queryLike(string $inputname = '', string $path = '', string $table = 'page_result', string $field = 'json_question_result'): array
    {
        $value = CIInput()->post_get($inputname) ?? "";
        $query = [];
        $jsonDoc = $this->db->dbprefix($table) . '.' . $field;

        if (empty($value) === false) {
            $query = ['JSON_VALUE(' . $jsonDoc . ',\'$.' . $path . '\') REGEXP' => "($value)"];
        }

        return $query;
    }

    protected function queryValue(string $inputname = '', string $path = '', string $table = 'page_result', string $field = 'json_question_result'): array
    {
        $value = CIInput()->post_get($inputname) ?? "";
        $query = [];
        $jsonDoc = $this->db->dbprefix($table) . '.' . $field;
        if (empty($value) === false) {
            $query = ['JSON_VALUE(' . $jsonDoc . ',\'$.' . $path . '\')' => $value];
            //$query = ['JSON_CONTAINS(' . $jsonDoc . ',JSON_OBJECT("' . $path . '","' .  $value . '")) > 0' => 'value_is_null'];
        }

        return $query;
    }
}
