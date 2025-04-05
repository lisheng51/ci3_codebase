<?php

class FailCheckTypeModel
{
    const userLogin = 1;
    const PasswordReset = 2;
    const UserRegister = 3;

    private static function userLogin(string $key = '')
    {
        $data['name'] = 'user_login';
        $data['description'] = null;
        $data['key'] = 'fail_user_login';
        $data['lock_time'] = 10;
        if (empty($key) === false) {
            return $data[$key];
        }
        return $data;
    }

    private static function passwordReset(string $key = '')
    {
        $data['name'] = 'password_reset';
        $data['description'] = null;
        $data['key'] = 'fail_password_reset';
        $data['lock_time'] = 300;
        if (empty($key) === false) {
            return $data[$key];
        }
        return $data;
    }

    private static function userRegister(string $key = '')
    {
        $data['name'] = 'user_register';
        $data['description'] = null;
        $data['key'] = 'fail_user_register';
        $data['lock_time'] = 600;
        if (empty($key) === false) {
            return $data[$key];
        }
        return $data;
    }

    public static function fetchField(int $id = 0, string $field = '')
    {
        switch ($id) {
            case 1:
                $value = self::userLogin($field);
                break;
            case 2:
                $value = self::passwordReset($field);
                break;
            case 3:
                $value = self::userRegister($field);
                break;
            default:
                $value = null;
                break;
        }
        return $value;
    }
}
