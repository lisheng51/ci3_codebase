<?php

class Message extends MX_Controller
{


    public function openai()
    {
        //https://api.telegram.org/bot668870743:AAH4doIE_Q9P392mwV3VwOYc9nOLMnZeG4o/getWebhookInfo
        //https://api.telegram.org/bot668870743:AAH4doIE_Q9P392mwV3VwOYc9nOLMnZeG4o/setWebhook?remove
        //https://api.telegram.org/bot668870743:AAH4doIE_Q9P392mwV3VwOYc9nOLMnZeG4o/setWebhook?url=https://2c19-87-212-152-135.eu.ngrok.io/myorder/webmaster/api/message/openai
        //https://api.telegram.org/bot668870743:AAH4doIE_Q9P392mwV3VwOYc9nOLMnZeG4o/setWebhook?url=https://webkassa.cafetariavanjet.nl/webmaster/api/message/openai

        $telegram = new My_telegram("668870743:AAH4doIE_Q9P392mwV3VwOYc9nOLMnZeG4o");
        $data = $telegram->getData();
        if ($data !== null) {
            $text = $telegram->Text();
            $chat_id = $telegram->ChatID();

            // Build a Tectalic OpenAI REST API Client globally.
            $yourApiKey = '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';
            $client = OpenAI::client($yourApiKey);

            try {
                /** @var \Tectalic\OpenAi\Models\ChatCompletions\CreateResponse $response */
                $response = $client->chat()->create([
                    'model' => 'gpt-3.5-turbo',
                    'messages' => [
                        ['role' => 'user', 'content' => $text],
                    ],
                ]);

                if ($response !== null) {
                    $result =  $response->choices[0]->message->content;
                    $content = ['parse_mode' => 'HTML', 'chat_id' => $chat_id, 'text' => $result];
                    $telegram->sendMessage($content);
                }
            } catch (ErrorException $e) {
                $content = ['parse_mode' => 'HTML', 'chat_id' => $chat_id, 'text' => $e->getMessage()];
                $telegram->sendMessage($content);
            }
        }
    }
}
