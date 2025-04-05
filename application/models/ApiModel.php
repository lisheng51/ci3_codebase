<?php

class ApiModel
{

    use BasicModel;
    public static $usingBodyOutSecurity = false;
    public static function __constructStatic()
    {
        self::$table = "api";
        self::$primaryKey = "api_id";
        self::$selectOrderBy = [
            'api_id#desc' => 'ID (aflopend)',
            'name#desc' => 'Naam (aflopend)',
            'name#asc' => 'Naam (oplopend)',
        ];
    }


    public static function select(int $id = 0, bool $with_empty = false, string $name = '', array $dataWhere = []): string
    {
        if ($id === 0) {
            $id = CIInput()->get(self::$primaryKey) ?? 0;
        }
        $where[self::$fieldIsDel] = 0;
        self::$sqlWhere = array_merge($where, $dataWhere);
        self::$sqlOrderBy = ['name' => 'asc'];
        $listdb = self::getAll();
        $select_name = !empty($name) ? $name : self::$primaryKey;
        $select = '<select name="' . $select_name . '" class="form-control">';
        $select .= $with_empty === true ? "<option value='' >------</option>" : "";
        foreach ($listdb as $rs) {
            $ckk = $id == $rs[self::$primaryKey] ? "selected" : '';
            $select .= '<option value=' . $rs[self::$primaryKey] . ' ' . $ckk . '>' . $rs["name"] . '</option>';
        }
        $select .= '</select>';
        return $select;
    }

    public static function selectMultiple(array $haystack = [], string $name = '', array $dataWhere = []): string
    {
        $select_name = empty($name) === false ? $name : self::$primaryKey;
        if (empty($haystack)) {
            $haystack = CIInput()->get(self::$primaryKey) ?? [];
            if (!is_array($haystack)) {
                $haystack = [$haystack];
            }
        }
        $where[self::$fieldIsDel] = 0;
        self::$sqlWhere = array_merge($where, $dataWhere);
        self::$sqlOrderBy = ['name' => 'asc'];
        $listdb = self::getAll();
        $select = '<select name="' . $select_name . '[]" class="form-control" multiple>';
        foreach ($listdb as $rs) {
            $ckk = (!empty($haystack) && in_array($rs[self::$primaryKey], $haystack)) ? 'selected' : '';
            $select .= '<option value=' . $rs[self::$primaryKey] . ' ' . $ckk . '>' . $rs["name"] . '</option>';
        }
        $select .= '</select>';
        return $select;
    }

    public static function getOneByRequest(): array
    {
        $headers = apache_request_headers();
        $apiId = $headers['Api-Id'] ?? $headers['api-id'] ?? $headers['Api-id'] ?? $headers['apiId'] ?? $headers['Apiid'] ?? $headers['apiid'] ?? 0;
        $apiSecret = $headers['Api-Key'] ?? $headers['api-key'] ?? $headers['Api-key'] ?? $headers['apiKey'] ?? $headers['Apikey'] ?? $headers['apikey'] ?? null;
        if (empty($apiId) || empty($apiSecret)) {
            return [];
        }

        $data[self::$primaryKey] = $apiId;
        $data['secret'] = $apiSecret;
        $data[self::$fieldIsDel] = 0;
        self::$sqlWhere = $data;
        return self::getOne();
    }

    public static function getVersion(string $v = "1.0"): string
    {
        $headers = apache_request_headers();
        return $headers['version'] ?? $v;
    }

    public static function fetchTokenMin(int $id = 0)
    {
        $much_strtotime = '+15 minutes';
        $rsdb = self::getOneById($id);
        if (!empty($rsdb)) {
            $much_strtotime = '+' . $rsdb['token_min'] . ' minutes';
        }
        return date('Y-m-d H:i:s', strtotime($much_strtotime));
    }

    public static function getPostdata(): array
    {
        $data["name"] = CIInput()->post("name");
        $data["max"] = CIInput()->post("max") ?? 0;
        $data["secret"] =  CIInput()->post("secret");
        $data["token_min"] = CIInput()->post("token_min") ?? 15;
        $permission_group_ids = CIInput()->post("permission_group_id");
        if (empty($permission_group_ids)) {
            $json["msg"] = 'Toestemming groep is leeg!';
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $data["permission_group_ids"] = implode(',', $permission_group_ids);
        AjaxckModel::value('name', $data["name"]);
        return $data;
    }


    public static function out($message = null, int $statusCode = 0, int $apiLogId = 0)
    {
        $output["message"] = $message;
        $output["statusCode"] = $statusCode;
        ApiLogModel::updateOut($apiLogId, $output);
        $headers = apache_request_headers();
        $type = $headers['type'] ?? $headers['Type'] ?? 'json';

        if (self::$usingBodyOutSecurity) {
            $encrypted_string = AesModel::encrypt(json_encode($output));
            $body = rawurlencode($encrypted_string);
            $output = [];
            $output["body"] = $body;
        }

        if ($type === 'json') {
            header('Content-Type: application/json');
            exit(json_encode($output));
        }

        $xml_data = new SimpleXMLElement('<?xml version="1.0"?><xmldata></xmldata>');
        self::_toXml($output, $xml_data);
        header('Content-Type: application/xml; charset=utf-8');
        exit($xml_data->asXML());
    }

    public static function outOK($message = 'good', int $apiLogId = 0)
    {
        self::out($message, 100, $apiLogId);
    }

    public static function outNOK(int $statusCode = 99, $message = 'error', int $apiLogId = 0)
    {
        self::out($message, $statusCode, $apiLogId);
    }

    private static function _toXml($data, &$xml)
    {
        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $key = 'item';
            }
            if (is_array($value)) {
                $subnode = $xml->addChild($key);
                self::_toXml($value, $subnode);
            } else {
                $xml->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }


    public static function checkCodeAccessDatetime(array $rsdb = [])
    {
        $headers = apache_request_headers();
        $code = $headers['code'] ?? $headers['Code'] ?? "";
        $apiId = $rsdb[self::$primaryKey];
        if (empty($code)) {
            self::outNOK(99, 'Code header is niet gevonden');
        }
        $key = rawurldecode($code);
        $arrTokenValue = json_decode(AesModel::decrypt($key), true);
        if ($arrTokenValue[self::$primaryKey] != $apiId) {
            self::outNOK(97, "Code kan niet verifiëren!");
        }

        $token_expires = $arrTokenValue['datetime'] ?? null;
        if (date("Y-m-d H:i:s") > $token_expires) {
            self::outNOK(95, "Code is verlopen!");
        }
    }

    public static function checkPermission(string $ids = "", int $statusCode = 92)
    {
        $listdbGroup = PermissionGroupModel::getAllGroup($ids);
        $stringCompares = array_column($listdbGroup, 'permission_ids');

        $permisson = $permissionArray = $all_permissions = $permissions = [];
        foreach ($stringCompares as $stringCompare) {
            $permissionArray[] = explode(',', $stringCompare);
        }
        $all_permissions = array_reduce($permissionArray, 'array_merge', []);
        $all_permissions = array_unique($all_permissions);

        if (empty($all_permissions) === false) {
            $listdb = PermissionModel::AllByPermissionIds($all_permissions);
            foreach ($listdb as $rsdb) {
                $path = $rsdb['use_path'] > 0 ? $rsdb['path'] : null;
                $permissions[] = $path . '.' . $rsdb['object'] . '.' . $rsdb['method'];
            }
        }

        $permisson = array_unique($permissions);
        $path = null;
        if (empty(CIRouter()->module) === false && CIRouter()->module !== 'api') {
            $path = CIRouter()->module;
        }
        $class = ucfirst(CIRouter()->class);
        $method = CIRouter()->method;
        $ckPath = $path . '.' . $class . '.' . $method;
        //self::out($ckPath, 90);
        $ckPermission = in_array($ckPath, $permisson);
        if ($ckPermission === false) {
            self::out('Niet gemachtigd', $statusCode);
        }
    }

    public static function checkFetchByJWT(bool $CheckExpires = true)
    {
        $headers = apache_request_headers();
        $apiId = $headers['Api-Id'] ?? $headers['api-id'] ?? $headers['Api-id'] ?? $headers['apiId'] ?? $headers['Apiid'] ?? $headers['apiid'] ?? 0;
        $token = $headers['token'] ?? $headers['Token'] ?? null;
        if (empty($token)) {
            self::outNOK(99, 'Token is niet gevonden');
        }
        $arrTokenValue = json_decode(CIEncryption()->decrypt(rawurldecode($token)), true);

        if ($arrTokenValue === null || empty($arrTokenValue) === true) {
            self::outNOK(97, "Token is niet juist");
        }

        $token_expires = $arrTokenValue['expires'] ?? null;
        if ($CheckExpires && date("Y-m-d H:i:s") > $token_expires) {
            self::outNOK(95, "Token is verlopen (tijd)");
        }

        $userId = $arrTokenValue[UserModel::$primaryKey] ?? 0;
        $dataUser = UserModel::getOneById($userId);
        if (empty($dataUser)) {
            self::outNOK(97, "Geen gebruiker gevonden");
        }

        $password_date = $arrTokenValue['password_date'] ?? null;
        if ($CheckExpires && $dataUser["password_date"] != $password_date) {
            self::outNOK(95, "Token is verlopen (wachtwoord)");
        }

        if ($arrTokenValue[self::$primaryKey] != $apiId) {
            self::outNOK(96, "Token misbruik(api)");
        }

        $ip_address = $arrTokenValue['ip_address'] ?? null;
        if ($ip_address !== null && $dataUser["ip_address"] != $ip_address) {
            self::outNOK(96, "Token misbruik(ip)");
        }

        return $dataUser;
    }
}
