<?php

class AjaxckModel
{
    const AllType = ["url", "username", "date", "datetime", "zipcode", "email", "phone", "youtube", "facebook", "twitter", "linkedin"];
    const SessionKey = "ajaxck_session";

    public static function toMuchSendMail(string $tem_key = '')
    {
        if (empty($tem_key)) {
            return;
        }
        $session = CISession()->tempdata($tem_key);
        if (empty($session) === false) {
            $json["msg"] = "Uw aanvraag was verzonden, Probeert u later nog eens";
            $json["status"] = "error";
            exit(json_encode($json));
        }
    }

    public static function spamword(array $data = [])
    {
        if (!SpamwordModel::check($data)) {
            $json["msg"] = "Spam woord gevonden!!";
            $json["status"] = "error";
            exit(json_encode($json));
        }
    }

    public static function failcheck(int $type_id = 0)
    {
        if (FailcheckModel::check($type_id) === false) {
            $json["msg"] = "Is gelockt, probeer het later opnieuw!";
            $json["status"] = "error";
            exit(json_encode($json));
        }
    }

    public static function password(string $password = "", int $min = 8, int $max = 32)
    {
        if (empty($password)) {
            $json["msg"] = "Wachtwoord is leeg!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        if (strlen(trim($password)) < $min || strlen(trim($password)) > $max) {
            $json["msg"] = "Wachtwoorden moet minimaal $min karakters en maximum $max karakters zijn";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $passObj = new Password_master();
        $config = array(
            'low_size' => 5,
            'upper_size' => 1,
            'number_size' => 1,
            'special_size' => 1,
            'check_password' => $password
        );

        $check_result = $passObj->checker("all", $config);
        if ($check_result === FALSE) {
            $json["msg"] = "Wachtwoorden moeten bestaan uit minimaal {$config['low_size']} kleine letter, {$config['upper_size']} hoofdletter, {$config['number_size']} cijfer en {$config['special_size']} speciaal teken, met maximum $max karakters.";
            $json["status"] = "error";
            exit(json_encode($json));
        }
    }

    public static function value(string $type = '', string $value = '', string $msg = '')
    {
        $json = [];
        if (empty($type)) {
            $json["msg"] = "Er is geen type gevonden!";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        if (empty(trim($value))) {
            $json["msg"] = empty($msg)  ? $type . " " . lang('required_error_text_global') : $msg;
            $json["status"] = "error";
            exit(json_encode($json));
        }

        if (in_array($type, self::AllType)) {
            if (self::isValidateValue($type, trim($value)) === false) {
                $json["msg"] = empty($msg)  ? $type . " formaat is niet juist" : $msg;
                $json["status"] = "error";
                exit(json_encode($json));
            }
        }
    }

    public static function length(string $str = '', int $min = 2, int $max = 50)
    {
        $len = strlen($str);
        if (empty($str) === true) {
            return true;
        }
        if ($len < $min || $len > $max) {
            return false;
        }
        return true;
    }

    private static function isValidateValue(string $type = '', string $value = ''): bool
    {
        if (empty($type) || empty($value)) {
            return false;
        }

        switch ($type) {
            case 'url':
                return (bool) filter_var($value, FILTER_VALIDATE_URL);
            case 'email':
                return (bool) filter_var($value, FILTER_VALIDATE_EMAIL);
            case 'phone':
                return (bool) preg_match('/^[0-9]{10}$/', $value);
            case 'username':
                return (bool) preg_match('/^[a-z0-9_-]{5,90}$/', $value);
            case 'zipcode';
                return (bool) preg_match('/([1-9]{1}[0-9]{3})([a-zA-Z]{2})$/', $value);
            case 'date':
                return F_datetime::validateDate($value);
            case 'datetime':
                return F_datetime::validateDate($value, 'Y-m-d H:i:s');
            case 'youtube':
                return (bool) preg_match('/^(https?:\/\/)?(www\.)?(?:youtube\.com|youtu\.be)\/[a-zA-Z0-9(\_\.\?)?]/', $value);
            case 'facebook':
                return (bool) preg_match('/^(https?:\/\/)?(www\.)?facebook.com\/[a-zA-Z0-9(\.\?)?]/', $value);
            case 'linkedin':
                return (bool) preg_match('/^(https?:\/\/)?(www\.)?linkedin.com\/[a-zA-Z0-9(\.\?)?]/', $value);
            case 'twitter':
                return (bool) preg_match('/^(https?:\/\/)?(www\.)?twitter.com\/[a-zA-Z0-9(\.\?)?]/', $value);
            default:
                return false;
        }

        return false;
    }

    public static function addSession(array $json = [])
    {
        CISession()->set_flashdata(self::SessionKey, $json);
    }

    public static function getSession(string $key = "msg", bool $getAll = false)
    {
        $session_data = CISession()->flashdata(self::SessionKey);
        if (empty($session_data) === false) {
            if ($getAll === true) {
                return $session_data;
            }
            return $session_data[$key];
        }
        return null;
    }

    public static function datetime(string $datetime = "", string $msg = "Datum tijd is niet juist!")
    {
        $result = [];
        $regEx = '/(\d{2})-(\d{2})-(\d{4}) (\d{2}):(\d{2}):(\d{2})/';
        preg_match($regEx, $datetime, $result);
        if (empty($result[0]) === true) {
            $json["msg"] = $msg;
            $json["status"] = "error";
            exit(json_encode($json));
        }
    }

    public static function date(string $date = "", string $msg = "Datum is niet juist!")
    {
        $result = [];
        $regEx = '/(\d{2})-(\d{2})-(\d{4})/';
        preg_match($regEx, $date, $result);
        if (empty($result[0]) === true) {
            $json["msg"] = $msg;
            $json["status"] = "error";
            exit(json_encode($json));
        }
    }
}
