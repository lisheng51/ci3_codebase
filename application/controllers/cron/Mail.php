<?php

class Mail extends Cron_Controller
{

    public function send(int $batchSite = 10)
    {
        $this->benchmark->mark('code_start');

        $ck_file_exist_path = config_item('cache_path') . 'cron_mail_send.txt';
        if (file_exists($ck_file_exist_path) === false) {
            write_file($ck_file_exist_path, 'klaar');
        }

        $status = file_get_contents($ck_file_exist_path);
        if ($status !== 'klaar') {
            TelegramModel::log(__METHOD__ . PHP_EOL . $status);
            exit;
        }

        write_file($ck_file_exist_path, 'bezig');

        $total_send = 0;
        $total_error = 0;
        $arrSys = SendMailModel::sendActionBulk($batchSite);
        $message = 'Sys ok: ' . $arrSys["total_send_ok"] . PHP_EOL;
        $message .= 'Sys error: ' . $arrSys["total_send_error"] . PHP_EOL;
        $message .= 'Sys batch: ' . $arrSys["total_batch"] . PHP_EOL;
        $total_send += $arrSys["total_send_ok"] + $arrSys["total_send_error"];
        $total_error += $arrSys["total_send_error"];

        write_file($ck_file_exist_path, 'klaar');

        $this->benchmark->mark('code_end');
        if ($total_error > 0) {
            $message .= 'Total Execution Time:' . $this->benchmark->elapsed_time('code_start', 'code_end');
            TelegramModel::log(__METHOD__ . PHP_EOL . $message);
        }
    }

    public function refresh_token()
    {
        $result["user"] = c_key('_core_smtp_user');
        $result["client_id"] = c_key('_core_smtp_client_id');
        $result["client_secret"] = c_key('_core_smtp_client_secret');
        $result["tenant_id"] = c_key('_core_smtp_tenant_id');
        $result["refresh_token"] = c_key('_core_smtp_user_refresh_token');

        if (!empty($result["refresh_token"])) {
            $newToken = MailConfigModel::getToken($result);
            ConfigModel::updateOne($newToken);
        }

        $listdb = MailConfigModel::getAllByField('is_del', 0);
        if (!empty($listdb)) {
            foreach ($listdb as $rsdb) {
                if (empty($rsdb['refresh_token'])) {
                    continue;
                }
                $newToken = MailConfigModel::getToken($rsdb);
                if (!empty($newToken)) {
                    MailConfigModel::edit($rsdb[MailConfigModel::$primaryKey], ['refresh_token' => $newToken]);
                }
            }
        }
    }
}
