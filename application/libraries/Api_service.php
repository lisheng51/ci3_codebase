<?php


class Api_service
{
    public static function curl(string $path = "", array $params = [])
    {
        $apiId = ENVIRONMENT_BC_API_ID;
        $apiKey = ENVIRONMENT_BC_API_KEY;

        if (empty($apiId) === true || empty($apiKey) === true) {
            return "";
        }

        $httpheaders = array(
            "api-id:  $apiId",
            "api-key: $apiKey"
        );
        $toUrl = ENVIRONMENT_BC_API_URL . $path;
        $fields_string = http_build_query($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HEADER, 0); //return url reponse header
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheaders);
        curl_setopt($ch, CURLOPT_URL, $toUrl);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return "";
        }
        curl_close($ch);
        return $response;
    }

    public static function selectNamePrefix(string $value = "", string $name = ''): string
    {
        $listdb = self::allNamePrefix();
        if (empty($listdb)) {
            return "";
        }
        $select_name = empty($name) === false ? $name : "prefix";
        $select = '<select name="' . $select_name . '" class="form-control">';
        $select .= "<option value='' ></option>";
        foreach ($listdb as $rs) {
            $ckk = $value == $rs['value'] ? "selected" : '';
            $select .= '<option value="' . $rs["value"] . '" ' . $ckk . '>' . $rs["value"] . '</option>';
        }
        $select .= '</select>';
        return $select;
    }

    public static function selectNationality(string $value = "", string $name = ''): string
    {
        $listdb = self::allNationality();
        if (empty($listdb)) {
            return "";
        }
        $select_name = empty($name) === false ? $name : "nationality";
        $select = '<select name="' . $select_name . '" class="form-control">';
        $select .= "<option value='' ></option>";
        foreach ($listdb as $rs) {
            $ckk = $value == $rs['nationality'] ? "selected" : '';
            $select .= '<option value="' . $rs["nationality"] . '" ' . $ckk . '>' . $rs["nationality"] . '</option>';
        }
        $select .= '</select>';
        return $select;
    }

    public static function allNationality(string $jsonFile = "country.json")
    {
        $response = get_cache_file($jsonFile);
        if (!isJSON($response)) {
            $response = self::curl('country/api/home/all');
            update_cache_file($jsonFile, $response);
        }
        $arrResult = [];
        if (isJSON($response)) {
            $response_result = json_decode($response, true);
            if ($response_result['statusCode'] === 100) {
                $arrResult = $response_result['message']["listdb"];
            }
        }
        return $arrResult;
    }

    public static function allNamePrefix(string $jsonFile = "name_prefix.json")
    {
        $response = get_cache_file($jsonFile);
        if (!isJSON($response)) {
            $response = self::curl('name_prefix/api/home/all');
            update_cache_file($jsonFile, $response);
        }
        $arrResult = [];
        if (isJSON($response)) {
            $response_result = json_decode($response, true);
            if ($response_result['statusCode'] === 100) {
                $arrResult = $response_result['message']["listdb"];
            }
        }
        return $arrResult;
    }

    public static function selectProvince(string $value = "", string $name = ''): string
    {
        $listdb = self::allProvince();
        if (empty($listdb)) {
            return "";
        }
        $select_name = empty($name) === false ? $name : "province";
        $select = '<select name="' . $select_name . '" class="form-control">';
        $select .= "<option value='' ></option>";
        foreach ($listdb as $rs) {
            $ckk = $value == $rs['name'] ? "selected" : '';
            $select .= '<option value="' . $rs["name"] . '" ' . $ckk . '>' . $rs["name"] . '</option>';
        }
        $select .= '</select>';
        return $select;
    }

    public static function allProvince(string $jsonFile = "province.json")
    {
        $response = get_cache_file($jsonFile);
        if (!isJSON($response)) {
            $response = self::curl('province/api/home/all');
            update_cache_file($jsonFile, $response);
        }
        $arrResult = [];
        if (isJSON($response)) {
            $response_result = json_decode($response, true);
            if ($response_result['statusCode'] === 100) {
                $arrResult = $response_result['message']["listdb"];
            }
        }
        return $arrResult;
    }


    public static function addressSearch(string $zipcode = "", string $houseNumber = "", string $housenr_addition = "")
    {
        $input["zipcode"] = $zipcode;
        $input["housenr"] = $houseNumber;
        $input["housenr_addition"] = $housenr_addition;

        $arrResult = [];
        $response = self::curl('address/api/home/search', $input);
        if (isJSON($response)) {
            $response_result = json_decode($response, true);
            if ($response_result['statusCode'] === 100) {
                $arrResult = $response_result['message'];
            }
        }
        return $arrResult;
    }


    public static function log(string $message = "", string $path = "error")
    {
        $content["message"] = $message;
        $content["site"] = ENVIRONMENT_BASE_URL;
        $content["database"] = ENVIRONMENT_DATABASE;
        $content["environment"] = ENVIRONMENT;

        $input['request_post'] = json_encode($_POST);
        $input['content'] = json_encode($content);
        $input['request_get'] = json_encode($_GET);
        $input['request_header'] = json_encode(apache_request_headers());

        $arrResult = [];
        $response = self::curl('log/api/home/' . $path, $input);
        if (isJSON($response)) {
            $response_result = json_decode($response, true);
            if ($response_result['statusCode'] === 100) {
                $arrResult = $response_result['message'];
            }
        }
        return $arrResult;
    }
}
