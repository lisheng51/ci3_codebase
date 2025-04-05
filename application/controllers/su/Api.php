<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Diflex
 *
 * @author Administrator
 */
class Api extends Su_Controller
{

    public function index()
    {
        $trest = urlencode("+DkZhtbz8HLxhp2c0XhDmxZqes5C0oAhT0+ew+eEYvw=");
        $params = [
            'only_test' => $trest, //'+DkZhtbz8HLxhp2c0XhDmxZqes5C0oAhT0+ew+eEYvw=',
            'license_name' => '1.2',
        ];
        $response = $this->curl(site_url('api/Whitelist'), $params);
        $obj_geo = json_decode($response);
        dump($obj_geo);
    }

    public function sendmail()
    {
        $file[] = ['file_name' => 'darktheme.js', 'base64' => 'bG9hZFRoZW1lID0gdHlwZW9mIGxvYWRUaGVtZSA9PT0gJ2Z1bmN0aW9uJyA/IGxvYWRUaGVtZSA6ICgpID0+IGxvY2FsU3RvcmFnZS5nZXRJdGVtKCd0aGVtZScpOw0Kc2F2ZVRoZW1lID0gdHlwZW9mIHNhdmVUaGVtZSA9PT0gJ2Z1bmN0aW9uJyA/IHNhdmVUaGVtZSA6IHRoZW1lID0+IGxvY2FsU3RvcmFnZS5zZXRJdGVtKCd0aGVtZScsIHRoZW1lKTsNCg0KY29uc3QgdGhlbWVDaGFuZ2VIYW5kbGVycyA9IFtdOw0KDQovLyA9PT09PT09PT09PT09PT0gSW5pdGlhbGl6YXRpb24gPT09PT09PT09PT09PT09DQoNCmluaXRUaGVtZSgpOw0KDQpjb25zdCBkYXJrU3dpdGNoID0gZG9jdW1lbnQuZ2V0RWxlbWVudEJ5SWQoJ2RhcmtTd2l0Y2gnKTsNCmlmIChkYXJrU3dpdGNoICE9IG51bGwpIHsNCiAgICBkYXJrU3dpdGNoLmNoZWNrZWQgPSBnZXRUaGVtZSgpID09PSAnZGFyayc7DQogICAgZGFya1N3aXRjaC5vbmNoYW5nZSA9ICgpID0+IHsNCiAgICAgICAgc2V0VGhlbWUoZGFya1N3aXRjaC5jaGVja2VkID8gJ2RhcmsnIDogJ2xpZ2h0Jyk7DQogICAgfTsNCn0NCg0KDQp0aGVtZUNoYW5nZUhhbmRsZXJzLnB1c2godGhlbWUgPT4gZGFya1N3aXRjaC5jaGVja2VkID0gdGhlbWUgPT09ICdkYXJrJyk7DQoNCi8vID09PT09PT09PT09PT09PSBNZXRob2RzID09PT09PT09PT09PT09PQ0KDQovLyBhZGFwdGVkIGZyb20gaHR0cHM6Ly9naXRodWIuY29tL2NvbGlmZi9kYXJrLW1vZGUtc3dpdGNoDQoNCmZ1bmN0aW9uIGluaXRUaGVtZSgpIHsNCiAgICBkaXNwbGF5VGhlbWUoZ2V0VGhlbWUoKSk7DQp9DQoNCmZ1bmN0aW9uIGdldFRoZW1lKCkgew0KICAgIHJldHVybiBsb2FkVGhlbWUoKSB8fCAod2luZG93Lm1hdGNoTWVkaWEoDQogICAgICAgICcocHJlZmVycy1jb2xvci1zY2hlbWU6IGRhcmspJykubWF0Y2hlcyA/ICdkYXJrJyA6ICdsaWdodCcpOw0KfQ0KDQpmdW5jdGlvbiBzZXRUaGVtZSh0aGVtZSkgew0KICAgIHNhdmVUaGVtZSh0aGVtZSk7DQogICAgZGlzcGxheVRoZW1lKHRoZW1lKTsNCn0NCg0KZnVuY3Rpb24gZGlzcGxheVRoZW1lKHRoZW1lKSB7DQogICAgZG9jdW1lbnQuYm9keS5zZXRBdHRyaWJ1dGUoJ2RhdGEtdGhlbWUnLCB0aGVtZSk7DQogICAgZm9yIChsZXQgaGFuZGxlciBvZiB0aGVtZUNoYW5nZUhhbmRsZXJzKSB7DQogICAgICAgIGhhbmRsZXIodGhlbWUpOw0KICAgIH0NCn0='];
        $attach_json  = json_encode($file);

        $bcc['lisheng51@gmail.com'] = 'Lisheng';
        $bcc['lisheng51@hotmail.com'] = 'Lisheng 2';
        $bcc_json  = json_encode($bcc);
        $params = [
            'bcc_json' => $bcc_json,
            'attach_json' => $attach_json,
            'subject' => 'Onderwerp A',
            'to_email' => 'l.ye@bloemendaalconsultancy.nl',
            'from_email' => 'development@bloemendaalconsultancy.nl',
            'message' => 'Message A',
        ];
        $response = $this->curl(site_url('api/sendmail/action'), $params);
        $obj_geo = json_decode($response);
        dump($obj_geo);
    }

    private function curl(string $url = "", array $params = [], string $apiId = "1", string $apiKey = "82444824b84c183e9bfabb8daa95bc7d")
    {
        if (empty($url) === true || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return;
        }
        $httpheaders = array(
            "apiId:  $apiId",
            "apiKey: $apiKey"
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HEADER, 0); //return url reponse header
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheaders);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        if (empty($params) === false) {
            $fields_string = http_build_query($params);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        }
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
