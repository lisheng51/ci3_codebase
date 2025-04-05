<?php

class AesModel
{
    public static $password = ENVIRONMENT_ENCRYPTION_PASS;
    public static $key = ENVIRONMENT_ENCRYPTION_KEY;

    private static function setOject(): AesEncryption
    {
        $aes = new AesEncryption();
        $aes->setMasterKey(self::$key);
        return $aes;
    }

    public static function encrypt(string $text = "")
    {
        $aes = self::setOject();
        if (empty($text)) {
            return "";
        }
        return $aes->encrypt($text, self::$password);
    }

    public static function decrypt(string $hash = "")
    {
        $aes = self::setOject();
        if (empty($hash)) {
            return "";
        }
        return $aes->decrypt($hash, self::$password);
    }
}
