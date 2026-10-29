<?php

class TelegramModel
{

    public static $groupChatId = ENVIRONMENT_TELEGRAM_GROUP;
    public static $default_chat_id = ENVIRONMENT_TELEGRAM_USER;
    public static $default_bot_token = ENVIRONMENT_TELEGRAM_BOT;
    public static $addLogExtra = true;
    private static $limit = 4000;
    private static $maxRetries = 3;

    public static function loadObject(string $setToken = ''): My_telegram
    {
        $bot_token = self::$default_bot_token;
        if (!empty($setToken)) {
            $bot_token = $setToken;
        }

        $object = new My_telegram($bot_token);
        return $object;
    }

    private static function logExtra(string $text = ''): string
    {
        if (!self::$addLogExtra) {
            return $text;
        }
        $text .= PHP_EOL . PHP_EOL . 'datetime: ' . date('d-m-Y H:i:s');
        $text .= PHP_EOL . 'timezone: ' . date_default_timezone_get();
        $text .= PHP_EOL . 'site: ' . ENVIRONMENT_BASE_URL;
        $text .= PHP_EOL . 'db: [' . ENVIRONMENT_DATABASE . '] ' . getDatabaseServerInfo();
        $text .= PHP_EOL . 'app: ' . c_key('_core_app_buildnr') . ' # php:' . phpversion();
        $text .= PHP_EOL . 'ip: ' . CIInput()->ip_address() . ' # user id:' . LoginModel::userId();
        $text .= PHP_EOL . 'path: ' . uri_string();
        $text .= PHP_EOL . 'web server: ' . getWebServerInfo();
        return $text;
    }

    public static function log(string $text = '', string $setChatId = '', array $button = [])
    {
        if (empty($text)) {
            return;
        }

        $chat_id = self::getChatId($setChatId);
        $currentMessage = self::logExtra($text);

        while (strlen($currentMessage) > self::$limit) {
            $part = substr($currentMessage, 0, self::$limit);
            self::send($part, $chat_id, $button);
            $currentMessage = substr($currentMessage, self::$limit);
            usleep(200000);
        }

        if (strlen($currentMessage) > 0) {
            self::send($currentMessage, $chat_id, $button);
        }
    }

    private static function send(string $text, string $chat_id, array $button = []): bool
    {
        $object = self::loadObject();
        $previewOptions = ['is_disabled' => true];
        $content = [
            'parse_mode' => 'HTML',
            'link_preview_options' => json_encode($previewOptions),
            'chat_id' => $chat_id,
            'text' => $text
        ];

        if (!empty($button)) {
            $content['reply_markup'] = json_encode($button);
        }

        $tries = 0;
        $success = false;

        while ($tries < self::$maxRetries && !$success) {
            try {
                $response = $object->sendMessage($content);
                if (!isset($response['ok']) || !$response['ok']) {
                    $desc = $response['description'] ?? 'Unknown Telegram error';
                    error_log("Telegram send error ($tries): " . $desc);
                    sleep(2);
                } else {
                    $success = true;
                }
            } catch (Throwable $ex) {
                error_log("Telegram exception: " . $ex->getMessage());
                sleep(2);
            }
            $tries++;
        }

        if (!$success) {
            $logfile = APPPATH . 'logs/telegram-failed-' . date('Y-m-d') . '.log';
            $msg = "[" . date('H:i:s') . "] Failed to send to {$chat_id}: " . mb_substr($text, 0, 1000) . PHP_EOL;
            @file_put_contents($logfile, $msg, FILE_APPEND);
        }

        return $success;
    }

    private static function getChatId(string $setChatId = ''): string
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
}
