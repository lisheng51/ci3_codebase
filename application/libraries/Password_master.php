<?php

class Password_master
{

    public $obj_checker;
    public $obj_generator;

    public function __construct()
    {
        $this->obj_checker = new Password_checker();
        $this->obj_generator = new Password_generator();
        log_message('debug', 'Password_master Class Initialized');
    }

    public function checker($type = "all", $config = array())
    {
        foreach ($config as $key => $value) {
            $this->obj_checker->$key = $value;
        }
        return $this->obj_checker->execute($type);
    }

    public function generator($getsource = FALSE, $config = array())
    {
        foreach ($config as $key => $value) {
            $this->obj_generator->$key = $value;
        }
        return $this->obj_generator->execute($getsource);
    }
}


class Password_master_core
{

    protected $low_size = 4;
    protected $upper_size = 2;
    protected $number_size = 1;
    protected $special_size = 1;
    protected $minlengte_size = 8;
    protected $check_password = "WElkom!1";

    public function __construct()
    {
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public function __set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }
}



class Password_generator extends Password_master_core
{

    public function execute($getsource = FALSE)
    {
        $password = $this->fetch_result_beter("lower") . $this->fetch_result_beter("upper") . $this->fetch_result_beter("number") . $this->fetch_result_beter("special");
        if ($getsource === TRUE) {
            return $password;
        }

        return str_shuffle($password);
    }

    private function fetch_result_beter($type = "lower")
    {
        switch ($type) {
            case "lower":
                $pool = 'abcdefghijklmnopqrstuvwxyz';
                $size = $this->low_size;
                break;
            case "upper":
                $pool = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $size = $this->upper_size;
                break;
            case "number":
                $pool = '0123456789';
                $size = $this->number_size;
                break;
            case "special":
                $pool = '@$*&+-#!';
                $size = $this->special_size;
                break;
            default:
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ@$*&+-#!';
                $size = $this->minlengte_size;
                break;
        }

        return substr(str_shuffle($pool), 0, $size);
    }

    private function fetch_result($type = "lower")
    {
        $arr_result = array();
        $string = "";
        switch ($type) {
            case "lower":
                $arr_result = range('a', 'z');
                $size = $this->low_size;
                break;
            case "upper":
                $arr_result = range('A', 'Z');
                $size = $this->upper_size;
                break;
            case "number":
                $arr_result = range(0, 9);
                $size = $this->number_size;
                break;
            case "special":
                $arr_result = array("@", "$", "*", "&", "+", "-", "#", "!");
                $size = $this->special_size;
                break;
            default:
                $arr_result = range('a', 'z');
                $size = $this->minlengte_size;
                break;
        }

        for ((int) $i = 1; (int) $i <= $size; (int) $i++) {
            $string .= $arr_result[array_rand($arr_result)];
        }
        return $string;
    }
}


class Password_checker extends Password_master_core
{

    private function fetch_result($type = "all")
    {

        switch ($type) {
            case "all":
                return $this->all();
            case "length":
                return strlen($this->check_password) >= $this->minlengte_size;
            case "upper":
                return preg_match_all("/[A-Z]/", $this->check_password) >= $this->upper_size;
            case "number":
                return preg_match_all("/[0-9]/", $this->check_password) >= $this->number_size;
            case "special":
                return preg_match_all("/[\W]+/", $this->check_password) >= $this->special_size;
            case "lower":
                return preg_match_all("/[a-z]/", $this->check_password) >= $this->low_size;
            default:
                return FALSE;
        }
        return FALSE;
    }

    public function execute($type = "all")
    {
        if (empty($this->check_password) === TRUE) {
            return FALSE;
        }
        return $this->fetch_result($type);
    }

    private function all()
    {
        $length = $this->execute("length");
        $upper = $this->execute("upper");
        $number = $this->execute("number");
        $special = $this->execute("special");
        $lower = $this->execute("lower");

        if ($length === TRUE && $upper === TRUE && $number === TRUE && $special === TRUE && $lower === TRUE) {
            return TRUE;
        }

        return FALSE;
    }
}
