<?php

class MailConfigModel
{
    use BasicModel;
    public static function __constructStatic()
    {
        self::$table = "mail_config";
        self::$primaryKey = "mail_config_id";
        self::$usingSoftDel = true;
        self::$selectOrderBy = [
            'mail_config_id#desc' => 'ID (aflopend)',
            'user#desc' => 'Gebruikersnaam (aflopend)',
            'user#asc' => 'Gebruikersnaam (oplopend)',
            'name#desc' => 'Afzender naam (aflopend)',
            'name#asc' => 'Afzender naam (oplopend)'
        ];
    }

    public static function getAzureProvider(array $mailConfig): PHPMailer\PHPMailer\OAuth
    {
        $clientId = $mailConfig["client_id"];
        $clientSecret = $mailConfig["client_secret"];
        $tenantId =  $mailConfig["tenant_id"];
        $refreshToken = $mailConfig["refresh_token"];

        $provider = new Greew\OAuth2\Client\Provider\Azure(
            [
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
                'tenantId' => $tenantId,
            ]
        );

        return new \PHPMailer\PHPMailer\OAuth(
            [
                'provider' => $provider,
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
                'refreshToken' => $refreshToken,
                'userName' => $mailConfig["Username"]
            ]
        );
    }

    public static function getToken(array $mailConfig, string $scope = 'https://outlook.office.com/SMTP.Send offline_access', bool $getAccessToken = false): string
    {
        $clientId = $mailConfig["client_id"];
        $clientSecret = $mailConfig["client_secret"];
        $name = $mailConfig["user"];
        $tenantId =  $mailConfig["tenant_id"];
        $refreshToken = $mailConfig["refresh_token"];

        $url = 'https://login.microsoftonline.com/' . $tenantId . '/oauth2/v2.0/token';
        $params = [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => $scope,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token'
        ];
        $response = get_curl($url, $params);
        $jsonResponse = json_decode($response);
        if ($getAccessToken && isset($jsonResponse->access_token)) {
            return $jsonResponse->access_token;
        }
        if (!$getAccessToken && isset($jsonResponse->refresh_token)) {
            return $jsonResponse->refresh_token;
        }
        TelegramModel::log(__METHOD__ . '=>' . $name . PHP_EOL . $response);
        return "";
    }

    public static function getGraphAccessToken(array $mailConfig): string
    {
        $client = new GuzzleHttp\Client();
        $response = $client->post('https://login.microsoftonline.com/' . $mailConfig["tenant_id"] . '/oauth2/v2.0/token', [
            'form_params' => [
                'client_id' => $mailConfig["client_id"],
                'client_secret' => $mailConfig["client_secret"],
                'scope' => 'https://graph.microsoft.com/.default',
                'grant_type' => 'client_credentials',
            ],
        ]);

        $tokenData = json_decode($response->getBody());
        return $tokenData->access_token ?? '';
    }

    public static function sendMailData(string $from_email = "")
    {
        $result["Host"] = c_key('_core_smtp_host');
        $result["Username"] = c_key('_core_smtp_user');
        $result["Password"] = GlobalModel::decryptData(c_key('_core_smtp_pass'));
        $result["SMTPSecure"] = c_key('_core_smtp_crypto');
        $result["Port"] = (int) c_key('_core_smtp_port');
        $result["Name"] = c_key('_core_smtp_user_name');
        $result["client_id"] = c_key('_core_smtp_client_id');
        $result["client_secret"] = c_key('_core_smtp_client_secret');
        $result["tenant_id"] = c_key('_core_smtp_tenant_id');
        $result["refresh_token"] = c_key('_core_smtp_user_refresh_token');

        if (!empty($from_email)) {
            $mailConfig = self::getOneByField('user', $from_email);
            if (!empty($mailConfig)) {
                $result["Host"] = $mailConfig['host'];
                $result["Username"] = $mailConfig['user'];
                $result["Password"] = GlobalModel::decryptData($mailConfig["pass"]);
                $result["SMTPSecure"] = $mailConfig['crypto'];
                $result["Port"] = (int) $mailConfig['port'];
                $result["Name"] = $mailConfig['name'];
                $result["client_id"] = $mailConfig['client_id'];
                $result["client_secret"] = $mailConfig['client_secret'];
                $result["tenant_id"] = $mailConfig['tenant_id'];
                $result["refresh_token"] = $mailConfig['refresh_token'];
            }
        }
        return $result;
    }

    public static function getPostdata(): array
    {
        $data["host"] = CIInput()->post("host");
        $data["user"] = CIInput()->post("user");
        $data["crypto"] = CIInput()->post("crypto");
        $data["port"] = CIInput()->post("port");
        $data["name"] = CIInput()->post("name");
        $pass = CIInput()->post("pass");
        $data["pass"] = GlobalModel::encryptData($pass);

        $data["client_id"] = CIInput()->post("client_id");
        $data["client_secret"] = CIInput()->post("client_secret");
        $data["tenant_id"] = CIInput()->post("tenant_id");
        $data["refresh_token"] = CIInput()->post("refresh_token");

        AjaxckModel::value('host', $data["host"]);
        AjaxckModel::value('user', $data["user"]);
        AjaxckModel::value('port', $data["port"]);
        AjaxckModel::value('name', $data["name"]);
        return $data;
    }

    public static function select(string $user = "", string $name = '', bool $with_empty = true, array $dataWhere = []): string
    {
        $select_name = empty($name) === false ? $name : self::$primaryKey;
        if (empty($user) === true) {
            $user = CIInput()->get($select_name) ?? "";
        }
        $where[self::$fieldIsDel] = 0;
        self::$sqlWhere = array_merge($where, $dataWhere);
        self::$sqlOrderBy = ['name' => 'asc'];
        $listdb = self::getAll();
        $select_name = !empty($name) ? $name : self::$primaryKey;
        $select = '<select name="' . $select_name . '" class="form-select">';
        $emptyOptionText = c_key('_core_smtp_user_name') . ' (' . c_key('_core_smtp_user') . ')';
        $select .= $with_empty === true ? '<option value="" >' . $emptyOptionText . '</option>' : '';
        foreach ($listdb as $rs) {
            $ckk = $user == $rs['user'] ? "selected" : '';
            $optionText = $rs["name"] . ' (' . $rs["user"] . ')';
            $select .= '<option value=' . $rs['user'] . ' ' . $ckk . '>' . $optionText . '</option>';
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
        $emptyOptionText = c_key('_core_smtp_user_name') . ' (' . c_key('_core_smtp_user') . ')';
        $select = '<select name="' . $select_name . '[]" class="form-select" multiple>';
        $select .= '<option value="' . c_key('_core_smtp_user') . '" >' . $emptyOptionText . '</option>';
        foreach ($listdb as $rs) {
            $ckk = (empty($haystack) === false && in_array($rs['user'], $haystack)) ? 'selected' : '';
            $optionText = $rs["name"] . ' (' . $rs["user"] . ')';
            $select .= '<option value=' . $rs['user'] . ' ' . $ckk . '>' . $optionText . '</option>';
        }
        $select .= '</select>';
        return $select;
    }
}
