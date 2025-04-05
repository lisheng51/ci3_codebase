<?php

class Aes extends API_Controller
{

    private $defaultTypeFun = 'usingSystem';

    private function usingGlobal(string $valuestring = "", string $type = "encrypt"): string
    {
        if ($type === 'encrypt') {
            return AesModel::encrypt($valuestring) ?? "";
        }
        return AesModel::decrypt($valuestring) ?? "";
    }

    private function usingSystem(string $valuestring = "", string $type = "encrypt"): string
    {
        if ($type === 'encrypt') {
            return GlobalModel::encryptData($valuestring);
        }
        return GlobalModel::decryptData($valuestring);
    }

    /**
     * @OA\Post(
     *     path="/api/aes/encrypt",
     *       tags={"core"},
     *     @OA\Response(response="200", description="An example resource")
     * )
     */
    public function encrypt()
    {
        $apiLogId = $this->addLog(__METHOD__);
        $data = CIInput()->post() ?? null;
        $data[ApiModel::$primaryKey] = $this->apiId;
        $typeFun = CIInput()->post('typeFun') ?? $this->defaultTypeFun;

        if (empty($typeFun) === true) {
            ApiModel::outNOK(99, "Geen typeFun gevonden!", $apiLogId);
        }

        $valuestring = json_encode($data);
        if (empty($valuestring) === true) {
            ApiModel::outNOK(99, "Geen data gevonden!", $apiLogId);
        }

        $tokenString = call_user_func_array([$this, $typeFun], [$valuestring, __FUNCTION__]);
        if (empty($tokenString) === true) {
            ApiModel::outNOK(99, "Geen data gevonden!", $apiLogId);
        }

        $token = rawurlencode($tokenString);
        ApiModel::outOK($token, $apiLogId);
    }

    /**
     * @OA\Post(
     *     path="/api/aes/decrypt",
     *       tags={"core"},
     *     @OA\Response(response="200", description="An example resource")
     * )
     */
    public function decrypt()
    {
        $apiLogId = $this->addLog(__METHOD__);
        $code = CIInput()->post('code') ?? null;
        $typeFun = CIInput()->post('typeFun') ?? $this->defaultTypeFun;

        if (empty($typeFun) === true) {
            ApiModel::outNOK(99, "Geen typeFun gevonden!", $apiLogId);
        }

        if (empty($code) === true) {
            ApiModel::outNOK(99, "Geen code gevonden!", $apiLogId);
        }
        $valuestring = rawurldecode($code);
        $json = call_user_func_array([$this, $typeFun], [$valuestring, __FUNCTION__]);
        if (empty($json) === true) {
            ApiModel::outNOK(99, "Geen data gevonden!", $apiLogId);
        }
        ApiModel::outOK($json, $apiLogId);
    }

    public function infoToken()
    {
        $apiLogId = $this->addLog(__METHOD__);
        $sec = CIInput()->post('sec') ?? 60;
        $json["password"] = random_string();
        $json["key"] = random_string('sha1');
        $json["datetime"] = date('Y-m-d H:i:s', time() + $sec);

        $valuestring = json_encode($json);
        $encrypted_string = GlobalModel::encryptData($valuestring);
        $hashkey = rawurlencode($encrypted_string);
        $json["token"] = $hashkey;

        ApiModel::outOK($json, $apiLogId);
    }
}
