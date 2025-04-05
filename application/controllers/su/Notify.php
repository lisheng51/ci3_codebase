<?php

class Notify extends Su_Controller
{

    public function index()
    {
        $chat_id = 273053602;
        $objectTelegram = TelegramModel::loadObject();
        //$message = $objectTelegram->getUpdates();  //before find group id must first deleteWebhook if has webhook
        //$objectTelegram->setWebhook('https://my.bloemendaalconsultancy.nl/search/api/telegram_webhook/index');
        $keyboard = array(
            "inline_keyboard" => [
                [
                    ['text' => 'Open', 'url' => 'https://www.google.com'],
                    ['text' => 'wujie', 'callback_data' => '/wujie'],
                ]
            ]
        );
        $text = "demo met keyboard";
        $content = ['chat_id' => $chat_id, 'text' => $text, 'reply_markup' => json_encode($keyboard)];
        $res = $objectTelegram->sendMessage($content);
        var_dump($res);
    }
}
