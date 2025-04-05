<?php

class Sendmail extends API_Controller
{
    public function action()
    {
        $apiLogId = $this->addLog(__METHOD__);
        $postData = empty($this->arrPost) ? CIInput()->post() : $this->arrPost;
        $message = $postData["message"] ?? null;
        $subject = $postData["subject"] ?? null;
        $emailaddress = $postData["to_email"] ?? null;
        if (empty($message) || empty($subject) || empty($emailaddress)) {
            ApiModel::out('Geen data gevonden', 98);
        }

        if ((bool) filter_var($emailaddress, FILTER_VALIDATE_EMAIL) === false) {
            ApiModel::out('Geen email gevonden', 98);
        }

        $from_email = $postData["from_email"] ?? null;
        if (!empty($from_email)) {
            $mailConfig = MailConfigModel::sendMailData($from_email);
            if ($mailConfig['Username'] !== $from_email) {
                ApiModel::out('Geen mail server gevonden', 98);
            }
            SendMailModel::$setFromEmail = $from_email;
        }

        $reply_to_json = $postData["reply_to_json"] ?? "";
        if (!empty($reply_to_json)  && isJSON($reply_to_json)) {
            SendMailModel::$setReplyTo = json_decode($reply_to_json, true);
        }

        $cc_json = $postData["cc_json"] ?? "";  //{"a@e.nl":"name","a@e2.nl":"name2"}
        if (!empty($cc_json) && isJSON($cc_json)) {
            SendMailModel::$setCc = json_decode($cc_json, true);
        }

        $bcc_json = $postData["bcc_json"] ?? "";
        if (!empty($bcc_json)  && isJSON($bcc_json)) {
            SendMailModel::$setBcc = json_decode($bcc_json, true);
        }

        $file = [];
        if (isset($_FILES['mailfile'])) {
            $file = $this->makeFiles($_FILES['mailfile']);
        }

        $attach_json = $postData["attach_json"] ?? "";
        if (empty($attach_json) === false && isJSON($attach_json)) {
            $file = json_decode($attach_json, true);
        }

        $dataMessage["content"] = $message;
        $dataMessage["title"] = $subject;

        $insert_data = SendMailModel::getPostdata($dataMessage, $emailaddress);
        $mailId = SendMailModel::add($insert_data);

        if ($mailId > 0) {
            SendMailFileModel::upload($mailId, $file);
            $rsdb = SendMailModel::getOneById($mailId);
            $output["preview_url"] = SendMailModel::createEvent($rsdb, 'sys');
            $output['id'] = $mailId;
            ApiModel::outOK($output, $apiLogId);
        }

        ApiModel::out('Verzonden is mislukt', 93);
    }

    private function makeFiles(array $files = [], string $inputname = "mailfile"): array
    {
        $file = [];
        if (is_array($files['name'])) {
            foreach ($files['name'] as $key => $image) {
                $_FILES[$inputname]['name'] = $files['name'][$key];
                $_FILES[$inputname]['type'] = $files['type'][$key];
                $_FILES[$inputname]['tmp_name'] = $files['tmp_name'][$key];
                $_FILES[$inputname]['error'] = $files['error'][$key];
                $_FILES[$inputname]['size'] = $files['size'][$key];
                $filename = $files['name'][$key];

                $file_ext = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $_FILES[$inputname]['name'];
                if ($_FILES[$inputname]['error'] == 0) {
                    if (move_uploaded_file($_FILES[$inputname]['tmp_name'], $file_ext) === true) {
                        $file_content = file_get_contents($file_ext);
                        $base64String = base64_encode($file_content);
                        $data['base64'] = $base64String;
                        $data['file_name'] = $filename;
                        $file[] = $data;
                        @unlink($file_ext);
                    }
                }
            }
        } else {
            $file_ext = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $_FILES[$inputname]['name'];
            if ($_FILES[$inputname]['error'] == 0) {
                if (move_uploaded_file($_FILES[$inputname]['tmp_name'], $file_ext) === true) {
                    $file_content = file_get_contents($file_ext);
                    $base64String = base64_encode($file_content);
                    $data['base64'] = $base64String;
                    $data['file_name'] = $files['name'];
                    $file[] = $data;
                    @unlink($file_ext);
                }
            }
        }

        return $file;
    }
}
