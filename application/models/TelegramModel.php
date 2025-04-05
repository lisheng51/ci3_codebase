<?php

class TelegramModel
{

    public static $groupChatId = ENVIRONMENT_TELEGRAM_GROUP;
    public static $default_chat_id = ENVIRONMENT_TELEGRAM_USER;
    private static $limit = 4000;

    public static function loadObject(string $setToken = ''): My_telegram
    {
        $bot_token = ENVIRONMENT_TELEGRAM_BOT;
        if (!empty($setToken)) {
            $bot_token = $setToken;
        }

        $object = new My_telegram($bot_token);
        return $object;
    }

    public static function log(string $text = '', string $setChatId = '')
    {
        if (empty($text)) {
            return;
        }

        $text .= PHP_EOL . 'datetime: ' . date('d-m-Y H:i:s');
        $text .= PHP_EOL . 'site: ' . ENVIRONMENT_BASE_URL;
        $text .= PHP_EOL . 'database: ' . ENVIRONMENT_DATABASE;
        $text .= PHP_EOL . 'version: ' . c_key('_core_app_buildnr');
        $text .= PHP_EOL . 'path: ' . uri_string();

        $chat_id = self::getChatId($setChatId);
        $currentMessage = $text;

        if (strlen($currentMessage) <= self::$limit) {
            self::send($currentMessage, $chat_id);
        } else {
            while (strlen($currentMessage) > self::$limit) {
                $rest = substr($currentMessage, 0,  self::$limit);
                self::send($rest, $chat_id);
                $currentMessage = substr($currentMessage, self::$limit);
            }

            if (strlen($currentMessage) > 0) {
                self::send($currentMessage, $chat_id);
            }
        }
    }

    private static function send(string $text = '', string $chat_id = '')
    {
        $content = ['parse_mode' => 'HTML', 'chat_id' => $chat_id, 'text' => $text];
        $object = self::loadObject();
        $res = $object->sendMessage($content);
        return $res["ok"];
    }

    private static function getChatId(string $setChatId = '')
    {
        if (!empty($setChatId)) {
            return $setChatId;
        }
        $groupChatId = self::$groupChatId;
        $userChatId = self::$default_chat_id;
        return (ENVIRONMENT === 'development') ? $userChatId : $groupChatId;
    }

    public static function callback_chatids()
    {
        $object = self::loadObject();
        //$object->endpoint('deleteWebhook', [], false);
        return $object->getUpdates();
    }

    public static function sms(string $recipients = "", $text = "")
    {
        $originator = c_key('webapp_messagebird_originator');
        $params = [
            'recipients' => $recipients,
            'originator' => $originator,
            'body' => $text
        ];

        $log_file_extension = config_item('log_file_extension');
        $log_path = config_item('log_path');
        $filename = $log_path . 'sms-' . date('Y-m-d') . '.' . $log_file_extension;
        $response = self::curlSMS('https://rest.messagebird.com/messages', $params);

        $data["time"] = date('H:i:s');
        $data["response"] = $response;
        $data["from_number"] = $originator;
        $data["to_number"] = $recipients;
        $data["body"] = $text;
        $string = json_encode($data);
        write_file($filename, $string . PHP_EOL, 'a');

        $object = json_decode($response);
        if (isset($object->id)) {
            return true;
        }

        return false;
    }

    public static function smsLogListByDate(string $date = ''): array
    {
        if (empty($date)) {
            $date = date('Y-m-d');
        }
        $log_file_extension = config_item('log_file_extension');
        $log_path = config_item('log_path');
        $filename = $log_path . 'sms-' . $date . '.' . $log_file_extension;

        $data = [];
        if (!file_exists($filename) || !is_readable($filename)) {
            return $data;
        }

        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgets($handle)) !== false) {
                $data[] = json_decode($row, true);
            }
            fclose($handle);
        }

        return $data;
    }

    private static function curlSMS(string $url = "", array $params = [])
    {
        if (empty($url) === true || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return;
        }
        $apiKey = c_key('webapp_messagebird_api_key');
        $httpheaders = array(
            "Authorization:  AccessKey $apiKey"
        );
        $fields_string = http_build_query($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HEADER, 0); //return url reponse header
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheaders);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100020);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return null;
        }
        curl_close($ch);
        if ($response === false || empty($response) === true) {
            return null;
        }
        return $response;
    }
}
