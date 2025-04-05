<?php

class BarcodeModel
{
    public static function app2web(string $token = '', int $min = 15): string
    {
        if (empty($token) === true) {
            return self::emptyCode();
        }
        $data['token'] = $token;
        $data['expires'] = date('Y-m-d H:i:s', strtotime('+' . $min . ' minutes'));
        $data['function'] = __FUNCTION__;
        return self::makeCode($data);
    }

    public static function web2app(): string
    {
        if (LoginModel::userId() <= 0) {
            return self::emptyCode();
        }
        $data['user_id'] = LoginModel::userId();
        $data['function'] = __FUNCTION__;
        return self::makeCode($data);
    }

    public static function showCodeInApp(int $userId = 0): string
    {
        if ($userId <= 0) {
            return self::emptyCode();
        }
        $data['user_id'] = $userId;
        $data['function'] = 'web2app';
        return self::makeCode($data);
    }

    public static function makeCode(array $data = [], bool $onlyCode = false): string
    {
        $valuestring = json_encode($data);
        $tokenString = CIEncryption()->encrypt($valuestring);
        $code = rawurlencode($tokenString);
        if ($onlyCode) {
            return $code;
        }
        $generator = new \chillerlan\QRCode\QRCode();
        return $generator->render($code);
    }

    public static function string2Data(string $encodeString = "", bool $asArray = true)
    {
        $valuestring = rawurldecode($encodeString);
        $tokenString = CIEncryption()->decrypt($valuestring);
        if (!$tokenString) {
            return [];
        }
        $arrTokenValue = json_decode($tokenString, $asArray);
        return $arrTokenValue;
    }

    private static function emptyCode(): string
    {
        return sys_asset_url('img/0.png');
    }

    public static function oneD(string $content = ""): string
    {
        $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
        $base64 = 'data:image/png;base64,' . base64_encode($generator->getBarcode($content, $generator::TYPE_CODE_128));
        return $base64;
    }
}
