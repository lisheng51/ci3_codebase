<?php

use GuzzleHttp\{Client, RequestOptions};
use PHPMailer\PHPMailer\PHPMailer;

class SendMailModel
{

    use BasicModel;
    public static $setFromEmail = "";
    public static $setReplyTo = [];
    public static $setCc = [];
    public static $setBcc = [];
    public static $setFile = [];
    public static $template = "_share/sendmail/default/template";
    public static $buttonTemplate = "_share/sendmail/default/template_button";
    public static $alwaysSend = false;
    public static $lastError = "";

    public static function __constructStatic()
    {
        self::$table = "send_mail";
        self::$primaryKey = "mail_id";
        self::$selectOrderBy = [
            'mail_id#desc' => 'ID (aflopend)',
            'subject#desc' => 'Onderwerp (aflopend)',
            'subject#asc' => 'Onderwerp (oplopend)',
            'send_date#desc' => 'Verzonden (aflopend)',
            'send_date#asc' => 'Verzonden (oplopend)',
        ];
    }

    public static function createEvent(array $insert = [], string $path = "sys_open")
    {
        if (empty($insert)) {
            return;
        }
        $encrypted_string = GlobalModel::encryptData($insert["event_value"] . "#_#" . $insert["to_email"]);
        $hashkey = rawurlencode($encrypted_string);
        return site_url("site/Mail/" . $path . "?hashkey=$hashkey");
    }

    public static function setTemplate(string $filename = '')
    {
        if (!empty($filename) && file_exists(APPPATH . 'views' . DIRECTORY_SEPARATOR . '_share' . DIRECTORY_SEPARATOR . 'sendmail' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR . $filename . '.php')) {
            self::$template = "_share/sendmail/default/$filename";
        }
    }

    public static function previewTemplate(array $data = []): string
    {
        $message["check_open_image"] = "";
        $message["content"] = $data["content"];
        $message["title"] = $data["title"];
        $message["webversion_button"] = "";
        $html = stripslashes(GlobalModel::loadView(self::$template, $message, true));
        return $html;
    }

    public static function preview($message = null, $subject = 'Voorbeeld', string $mail_to_address = "")
    {
        $data["content"] = $message;
        $data["title"] = $subject;
        return self::addSend($data, $mail_to_address);
    }

    public static function messageCopie(int $message_id = 0, array $user_db = [])
    {
        if (empty($user_db) || $message_id <= 0) {
            return false;
        }
        $emailaddress = $user_db["emailaddress"];
        $template = 'message_copie';

        $configValue = MailTempModel::getByTrigger($template);
        $body = $configValue[$template . '_body'];
        $subject = $configValue[$template . '_subject'];

        $body = MailTemplateModel::replaceBody($body, $user_db);

        $data_button["button_url"] = login_url('?redirect_url=' . site_url(AccessCheckModel::$backPath . "/message/view/$message_id"));
        $data_button["button_text"] = "Hier";
        $button = GlobalModel::loadView(self::$buttonTemplate, $data_button, true);
        $body = str_replace('{sys_button}', $button, $body);

        $data["title"] = $subject;
        $data["content"] = $body;
        $data["app_log"] = "message_copie gestuurd naar gebruiker";
        $data["app_log_user_id"] = $user_db["user_id"];
        return self::addSend($data, $emailaddress);
    }

    public static function userActiveConfirm(array $user_db = [])
    {
        if (empty($user_db)) {
            return false;
        }
        $template = 'user_active_confirm';
        $emailaddress = $user_db["emailaddress"];
        $configValue = MailTempModel::getByTrigger($template);
        $body = $configValue[$template . '_body'];
        $subject = $configValue[$template . '_subject'];
        $body = MailTemplateModel::replaceBody($body, $user_db);

        $data_button["button_url"] = login_url();
        $data_button["button_text"] = "Hier";
        $button = GlobalModel::loadView(self::$buttonTemplate, $data_button, true);
        $body = str_replace('{sys_button}', $button, $body);

        $data["title"] = $subject;
        $data["content"] = $body;
        $data["app_log"] = "user_active_confirm gestuurd naar nieuwe gebruiker";
        $data["app_log_user_id"] = $user_db["user_id"];
        return self::addSend($data, $emailaddress);
    }

    public static function passwordReset(array $user_db = [])
    {
        if (empty($user_db)) {
            return false;
        }
        $template = 'password_reset';
        $emailaddress = $user_db["emailaddress"];
        $arr_reset = LoginModel::updatePasswordResetDate($user_db["user_id"]);
        $maxTimeStampText = F_datetime::convert_datetime($arr_reset["password_reset_date"]);
        $encrypted_string = GlobalModel::encryptData($user_db["user_id"] . "#_#" . $emailaddress);
        $hashkey = rawurlencode($encrypted_string);

        $configValue = MailTempModel::getByTrigger($template);
        $body = $configValue[$template . '_body'];
        $subject = $configValue[$template . '_subject'];

        $body = MailTemplateModel::replaceBody($body, $user_db);
        $body = str_replace('{sys_reset_time}', $maxTimeStampText, $body);

        $data_button["button_url"] = site_url(ENVIRONMENT_ACCESS_URL . "/password_reset/reset?hashkey=$hashkey");
        $data_button["button_text"] = "Wachtwoord instellen";
        $button = GlobalModel::loadView(self::$buttonTemplate, $data_button, true);

        $body = str_replace('{sys_button}', $button, $body);

        $data["title"] = $subject;
        $data["content"] = $body;
        $data["app_log"] = "password_reset gestuurd naar nieuwe gebruiker";
        $data["app_log_user_id"] = $user_db["user_id"];
        return self::addSend($data, $emailaddress);
    }

    public static function accessCodeLogin(array $user_db = [], string $password = '')
    {
        if (empty($user_db) === true) {
            return false;
        }
        $template = 'access_code_login';
        $arr_acces = LoginModel::updateAccessCodeData($user_db["user_id"]);
        $encrypted_string = GlobalModel::encryptData($user_db["user_id"] . "#_#" . $arr_acces["access_code"]);
        $hashkey = rawurlencode($encrypted_string);

        if (ENVIRONMENT === 'development') {
            TelegramModel::log('Toegangscode: ' . $arr_acces["access_code"]);
        }
        $emailaddress = $user_db["emailaddress"];
        $configValue = MailTempModel::getByTrigger($template);
        $body = $configValue[$template . '_body'];
        $subject = $configValue[$template . '_subject'];

        $body = MailTemplateModel::replaceBody($body, $user_db);
        $body = str_replace('{sys_access_code}', $arr_acces["access_code"], $body);
        $body = str_replace('{sys_access_code_date}', $arr_acces["access_code_date"], $body);

        $data_button["button_url"] = login_url();
        $data_button["button_text"] = "Inloggen";

        if (empty($password) === false) {
            $data_button["button_url"] = login_url("/direct?hashkey=$hashkey");
            $data_button["button_text"] = "Of direct inloggen";
        }

        $button = GlobalModel::loadView(self::$buttonTemplate, $data_button, true);
        $body = str_replace('{sys_button}', $button, $body);

        $data["title"] = $subject;
        $data["content"] = $body;
        $data["app_log"] = "access_code_login gestuurd naar nieuwe gebruiker";
        $data["app_log_user_id"] = $user_db["user_id"];
        return self::addSend($data, $emailaddress);
    }

    public static function editEmailCode(array $user_db = [], string $emailaddress = '')
    {
        if (empty($user_db) || empty($emailaddress)) {
            return false;
        }
        $template = 'edit_email_code';
        $code = F_string::random(6);
        CISession()->set_userdata('edit_email_code', $code);
        $old_email = $user_db["emailaddress"];

        $configValue = MailTempModel::getByTrigger($template);
        $body = $configValue[$template . '_body'];
        $subject = $configValue[$template . '_subject'];

        $body = MailTemplateModel::replaceBody($body, $user_db);
        $body = str_replace('{sys_code}', $code, $body);
        $body = str_replace('{sys_old_email}', $old_email, $body);
        $body = str_replace('{sys_new_email}', $emailaddress, $body);

        $data["title"] = $subject;
        $data["content"] = $body;

        $data["app_log"] = "edit_email_code gestuurd naar nieuwe gebruiker";
        $data["app_log_user_id"] = $user_db["user_id"];
        return self::addSend($data, $emailaddress);
    }

    public static function userRegister($user_db = [])
    {
        if (empty($user_db)) {
            return false;
        }

        $template = 'user_register';
        $emailaddress = $user_db["emailaddress"];
        $configValue = MailTempModel::getByTrigger($template);
        $body = $configValue[$template . '_body'];
        $subject = $configValue[$template . '_subject'];

        $body = MailTemplateModel::replaceBody($body, $user_db);

        $data["title"] = $subject;
        $data["content"] = $body;
        $data["app_log"] = "user_register gestuurd naar nieuwe gebruiker";
        $data["app_log_user_id"] = $user_db["user_id"];
        return self::addSend($data, $emailaddress);
    }

    public static function userActive(array $user_db = [])
    {
        if (empty($user_db)) {
            return false;
        }
        $template = 'user_active';
        $emailaddress = $user_db["emailaddress"];
        $encrypted_string = GlobalModel::encryptData($user_db["user_id"] . "#_#" . $emailaddress);
        $hashkey = rawurlencode($encrypted_string);

        $configValue = MailTempModel::getByTrigger($template);
        $body = $configValue[$template . '_body'];
        $subject = $configValue[$template . '_subject'];

        $body = MailTemplateModel::replaceBody($body, $user_db);

        $data_button["button_url"] = login_url("/active?hashkey=$hashkey");
        $data_button["button_text"] = "Account activeren";
        $button = GlobalModel::loadView(self::$buttonTemplate, $data_button, true);

        $body = str_replace('{sys_button}', $button, $body);

        $data["title"] = $subject;
        $data["content"] = $body;
        $data["app_log"] = "user_active gestuurd naar nieuwe gebruiker";
        $data["app_log_user_id"] = $user_db["user_id"];
        return self::addSend($data, $emailaddress);
    }

    public static function addSend(array $data = [], $emailaddress = null, $attach = null): bool
    {
        $mailId = self::addOnly($data, $emailaddress, $attach);
        if ($mailId > 0) {
            SendMailFileModel::upload($mailId, self::$setFile);
            $arr_mail = SendMailModel::getOneById(intval($mailId));
            $sendstatus = self::sendAction($arr_mail);
            if ($sendstatus) {
                $dataedit["is_send"] = 1;
                $dataedit["send_date"] = date('Y-m-d H:i:s');
                return self::edit($mailId, $dataedit);
            }
        }
        return false;
    }

    public static function addOnly(array $data = [], $emailaddress = null, $attach = null): int
    {
        $insert_data = self::getPostdata($data, $emailaddress, $attach);
        $mailId = self::add($insert_data);
        return $mailId;
    }

    public static function getPostdata(array $data = [], $emailaddress = null, $attach = null)
    {
        $insert["from_email"] = empty(self::$setFromEmail) === false ? self::$setFromEmail : c_key('_core_smtp_user');
        $insert["to_email"] = $emailaddress;
        $insert["subject"] = stripslashes($data["title"]);
        $insert["event_value"] = time();
        $insert["reply_to_json"] = json_encode(self::$setReplyTo);
        $insert["cc_json"] = json_encode(self::$setCc);
        $insert["bcc_json"] = json_encode(self::$setBcc);
        $message["content"] = $data["content"];
        $message["title"] = $data["title"];

        $data_button["button_url"] = self::createEvent($insert, 'sys');
        $data_button["button_text"] = "Bekijk de web versie";
        $button = GlobalModel::loadView(self::$buttonTemplate, $data_button, true);
        $message["webversion_button"] = intval(c_key('_core_sendmail_with_webversionurl')) > 0 ? $button : "";

        $image = '<img src="' .  self::createEvent($insert) . '" alt="image" style="display:none;width:0;height:0;visibility:hidden;">';
        $message["check_open_image"] = intval(c_key('_core_sendmail_with_check_open_img')) > 0 ? $image : "";

        $insert["message"] = GlobalModel::loadView(self::$template, $message, true);
        $stringAttach = $attach;
        if (is_array($attach)) {
            $stringAttach = implode(",", $attach);
        }
        $insert["attach"] = $stringAttach;
        return $insert;
    }

    public static function editMulti(array $ids = [], array $data = [])
    {
        if (!empty($ids)) {
            CIDb()->where_in(self::$primaryKey, $ids)->update(self::$table, $data);
        }
    }


    public static function sendActionBulk(int $batchSite = 30): array
    {
        $total_send_ok = 0;
        $total_send_error = 0;
        self::$sqlOrderBy = [self::$primaryKey => 'asc'];
        self::$sqlWhere = ["is_send" => 0];
        $listdb = self::getList($batchSite);

        if (!empty($listdb)) {
            $edit_ids = [];
            foreach ($listdb as $data) {
                $sendstatus = self::sendAction($data);
                if ($sendstatus) {
                    $total_send_ok++;
                    $edit_ids[] = $data[self::$primaryKey];
                } else {
                    $total_send_error++;
                }
            }
            $dataedit["is_send"] = 1;
            $dataedit["send_date"] = date('Y-m-d H:i:s');
            self::editMulti($edit_ids, $dataedit);
        }

        $arr["total_batch"] = $batchSite;
        $arr["total_send_ok"] = $total_send_ok;
        $arr["total_send_error"] = $total_send_error;
        return $arr;
    }

    public static function sendAction(array $data = [], array $setConfig = []): bool
    {
        self::$lastError = "";
        $from_email = "";
        if (isset($data['from_email']) && empty($data['from_email']) === false) {
            $from_email = $data["from_email"];
        }

        if (empty($from_email)) {
            return false;
        }

        $to_email = null;
        if (isset($data['to_email']) && empty($data['to_email']) === false) {
            $to_email = $data["to_email"];
        }
        if (empty($to_email)) {
            return false;
        }
        $subject = $data["subject"];
        $message = $data["message"];

        $mailConfig = MailConfigModel::sendMailData($from_email);
        if (!empty($setConfig)) {
            $mailConfig = $setConfig;
        }

        if (defined('ENVIRONMENT_SENDMAIL_MICROSOFT') && ENVIRONMENT_SENDMAIL_MICROSOFT === true) {
            return self::sendActionGraph($data, $mailConfig);
        }

        $mail = new PHPMailer(true);
        if (!empty($mailConfig['refresh_token'])) {
            $mail->AuthType = 'XOAUTH2';
            $mail->setOAuth(MailConfigModel::getAzureProvider($mailConfig));
        }

        try {
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                   
            $mail->isSMTP();
            $mail->SMTPAuth = true;
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $mail->Host = $mailConfig['Host'];
            $mail->Username = $mailConfig['Username'];
            $mail->Password = $mailConfig['Password'];
            $mail->SMTPSecure = $mailConfig['SMTPSecure'];
            $mail->Port = $mailConfig['Port'];
            $mail->setFrom($mailConfig['Username'], $mailConfig['Name']);
            $mail->addAddress($to_email);

            if (isset($data['reply_to_json']) && empty($data['reply_to_json']) === false) {
                $listReplyTo = json_decode($data["reply_to_json"], true);
                if (empty($listReplyTo) === false) {
                    foreach ($listReplyTo as $email => $name) {
                        $mail->addReplyTo($email, $name);
                    }
                }
            }

            if (isset($data['cc_json']) && empty($data['cc_json']) === false) {
                $listCc = json_decode($data["cc_json"], true);
                if (empty($listCc) === false) {
                    foreach ($listCc as $email => $name) {
                        $mail->addCC($email, $name);
                    }
                }
            }

            if (isset($data['bcc_json']) && empty($data['bcc_json']) === false) {
                $listBcc = json_decode($data["bcc_json"], true);
                if (empty($listBcc) === false) {
                    foreach ($listBcc as $email => $name) {
                        $mail->addBCC($email, $name);
                    }
                }
            }

            $files = SendMailFileModel::getAllByField(SendMailModel::$primaryKey, $data[SendMailModel::$primaryKey]);
            if (empty($files) === false) {
                foreach ($files as $rsdb) {
                    $file_content = base64_decode($rsdb["base64"]);
                    $filename = $rsdb["file_name"];
                    $mail->addStringAttachment($file_content, $filename);
                }
            }

            if (isset($data['attach']) && empty($data['attach']) === false) {
                $multiAttach = explode(",", $data['attach']);
                if (empty($multiAttach)) {
                    $mail->addAttachment($data["attach"]);
                } else {
                    foreach ($multiAttach as $value) {
                        $mail->addAttachment($value);
                    }
                }
            }
            $mail->isHTML();
            $mail->Subject = $subject;
            $mail->Body = $message;
            if (ENVIRONMENT !== 'development' || self::$alwaysSend) {
                return $mail->send();
            }
            return true;
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            self::$lastError = strip_tags($e->errorMessage());
            log_message('error', __METHOD__ . PHP_EOL . $e->errorMessage()  . ' => ' . $from_email);
            return false;
        } catch (Exception $e) {
            self::$lastError = $mail->ErrorInfo ?: $e->getMessage();
            log_message('error', __METHOD__ . PHP_EOL . $mail->ErrorInfo  . ' => ' . $from_email);
            return false;
        }
    }

    private const GRAPH_SMALL_ATTACH = 3000000;
    private const GRAPH_CHUNK = 327680 * 10;

    private static function sendActionGraph(array $data, array $mailConfig): bool
    {
        $from_email = $data["from_email"];
        $toRecipients = fn(array $list) => array_map(
            fn($email, $name) => ['emailAddress' => ['address' => $email, 'name' => (string) $name]],
            array_keys($list),
            $list
        );

        $message = [
            'subject' => $data["subject"],
            'body' => ['contentType' => 'HTML', 'content' => $data["message"]],
            'toRecipients' => [['emailAddress' => ['address' => $data["to_email"]]]],
        ];

        foreach (['replyTo' => 'reply_to_json', 'ccRecipients' => 'cc_json', 'bccRecipients' => 'bcc_json'] as $graphKey => $dataKey) {
            $list = empty($data[$dataKey]) ? [] : json_decode($data[$dataKey], true);
            if (!empty($list)) {
                $message[$graphKey] = $toRecipients($list);
            }
        }

        $attachments = [];
        $files = SendMailFileModel::getAllByField(SendMailModel::$primaryKey, $data[SendMailModel::$primaryKey]);
        foreach ($files as $rsdb) {
            $content = base64_decode($rsdb["base64"]);
            $attachments[] = ['name' => $rsdb["file_name"], 'size' => strlen($content), 'content' => $content];
        }

        if (!empty($data['attach'])) {
            foreach (explode(",", $data['attach']) as $path) {
                $attachments[] = ['name' => basename($path), 'size' => filesize($path), 'path' => $path];
            }
        }

        if (ENVIRONMENT === 'development' && !self::$alwaysSend) {
            return true;
        }

        $messageId = '';
        $client = new Client();
        $baseUrl = "https://graph.microsoft.com/v1.0/users/{$mailConfig['Username']}";
        try {
            $headers = ['Authorization' => "Bearer " . MailConfigModel::getGraphAccessToken($mailConfig)];
            $totalSize = array_sum(array_column($attachments, 'size'));

            if ($totalSize < self::GRAPH_SMALL_ATTACH) {
                foreach ($attachments as $att) {
                    $message['attachments'][] = self::graphFileAttachment($att);
                }
                $response = $client->post("$baseUrl/sendMail", [
                    RequestOptions::HEADERS => $headers,
                    RequestOptions::JSON => ['message' => $message, 'saveToSentItems' => true],
                ]);
                return $response->getStatusCode() === 202;
            }

            $draft = $client->post("$baseUrl/messages", [
                RequestOptions::HEADERS => $headers,
                RequestOptions::JSON => $message,
            ]);
            $messageId = json_decode($draft->getBody()->getContents(), true)['id'];

            foreach ($attachments as $att) {
                if ($att['size'] < self::GRAPH_SMALL_ATTACH) {
                    $client->post("$baseUrl/messages/$messageId/attachments", [
                        RequestOptions::HEADERS => $headers,
                        RequestOptions::JSON => self::graphFileAttachment($att),
                    ]);
                    continue;
                }
                self::graphUploadLargeAttachment($client, $headers, "$baseUrl/messages/$messageId", $att);
            }

            $response = $client->post("$baseUrl/messages/$messageId/send", [RequestOptions::HEADERS => $headers]);
            return $response->getStatusCode() === 202;
        } catch (\Throwable $e) {
            self::$lastError = $e->getMessage();
            if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
                self::$lastError = $e->getResponse()->getStatusCode() . ' ' . $e->getResponse()->getBody()->getContents();
            }
            log_message('error', __METHOD__ . PHP_EOL . self::$lastError . ' => ' . $from_email);

            if ($messageId !== '') {
                try {
                    $client->delete("$baseUrl/messages/$messageId", [RequestOptions::HEADERS => $headers]);
                } catch (\Throwable $e2) {
                    log_message('error', __METHOD__ . ' delete draft: ' . $e2->getMessage());
                }
            }
            return false;
        }
    }

    private static function graphFileAttachment(array $att): array
    {
        $content = $att['content'] ?? file_get_contents($att['path']);
        return [
            '@odata.type' => '#microsoft.graph.fileAttachment',
            'name' => $att['name'],
            'contentBytes' => base64_encode($content),
        ];
    }

    private static function graphUploadLargeAttachment(Client $client, array $headers, string $messageUrl, array $att): void
    {
        $session = $client->post("$messageUrl/attachments/createUploadSession", [
            RequestOptions::HEADERS => $headers,
            RequestOptions::JSON => ['AttachmentItem' => [
                'attachmentType' => 'file',
                'name' => $att['name'],
                'size' => $att['size'],
            ]],
        ]);
        $uploadUrl = json_decode($session->getBody()->getContents(), true)['uploadUrl'];

        $handle = isset($att['content']) ? null : fopen($att['path'], 'rb');
        try {
            for ($start = 0; $start < $att['size']; $start += self::GRAPH_CHUNK) {
                $length = min(self::GRAPH_CHUNK, $att['size'] - $start);
                $chunk = $handle ? fread($handle, $length) : substr($att['content'], $start, $length);
                $end = $start + $length - 1;
                // uploadUrl is pre-authorized: no Authorization header here
                $client->put($uploadUrl, [
                    RequestOptions::HEADERS => [
                        'Content-Length' => $length,
                        'Content-Range' => "bytes $start-$end/{$att['size']}",
                        'Content-Type' => 'application/octet-stream',
                    ],
                    RequestOptions::BODY => $chunk,
                ]);
            }
        } finally {
            if ($handle) {
                fclose($handle);
            }
        }
    }
}
