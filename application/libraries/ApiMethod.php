<?php

class ApiMethod
{
    private static $instance;

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private static function parseRawHttpRequest(array &$a_data)
    {
        // read incoming data
        $input = file_get_contents('php://input');

        // grab multipart boundary from content type header
        preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);

        // content type is probably regular form-encoded
        if (!count($matches)) {
            // we expect regular puts to containt a query string containing data
            parse_str(urldecode($input), $a_data);
            return $a_data;
        }

        $boundary = $matches[1];

        // split content by boundary and get rid of last -- element
        $a_blocks = preg_split("/-+$boundary/", $input);
        array_pop($a_blocks);

        // loop data blocks
        foreach ($a_blocks as $id => $block) {
            if (empty($block))
                continue;

            // you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char

            // parse uploaded files
            if (strpos($block, 'application/octet-stream') !== FALSE) {
                // match "name", then everything after "stream" (optional) except for prepending newlines
                preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
                $a_data['files'][$matches[1]] = $matches[2];
            }
            // parse all other fields
            else {
                // match "name" and optional value in between newline sequences
                preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
                $a_data[$matches[1]] = $matches[2];
            }
        }
    }

    public static function put(string $msg = 'Methode niet toegestaan')
    {
        self::_message($msg, __FUNCTION__);
        $array = [];
        self::parseRawHttpRequest($array);
        return $array;
    }

    public static function post(string $msg = 'Methode niet toegestaan')
    {
        self::_message($msg, __FUNCTION__);
        return $_POST;
    }

    public static function get(string $msg = 'Methode niet toegestaan')
    {
        self::_message($msg, __FUNCTION__);
        return $_GET;
    }

    public static function delete(string $msg = 'Methode niet toegestaan')
    {
        self::_message($msg, __FUNCTION__);
        $array = [];
        self::parseRawHttpRequest($array);
        return $array;
    }

    public static function response(int $statusCode = 0, $message = null)
    {
        $output["message"] = $message;
        $output["statusCode"] = $statusCode;
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        exit(json_encode($output));
    }

    private static function _message(string $msg = 'Methode niet toegestaan', string $method = "post")
    {
        $statusCode = 405;
        $methodCk = strtoupper($method);
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $method = $_SERVER['REQUEST_METHOD'];
            $ckString = strtoupper($method);
            if ($ckString !== $methodCk) {
                http_response_code($statusCode);
                $json["status"] = 'error';
                $json["msg"] = $msg;
                $output["message"] = $json;
                $output["statusCode"] = $statusCode;
                header('Content-Type: application/json; charset=utf-8');
                exit(json_encode($output));
            }
        }
    }

    public static function head(string $msg = 'Methode niet toegestaan')
    {
        self::_message($msg, __FUNCTION__);
        $array = [];
        self::parseRawHttpRequest($array);
        return $array;
    }

    public static function options(string $msg = 'Methode niet toegestaan')
    {
        self::_message($msg, __FUNCTION__);
        $array = [];
        self::parseRawHttpRequest($array);
        return $array;
    }

    public static function patch(string $msg = 'Methode niet toegestaan')
    {
        self::_message($msg, __FUNCTION__);
        $array = [];
        self::parseRawHttpRequest($array);
        return $array;
    }

    public static function view(string $msg = 'Methode niet toegestaan')
    {
        self::_message($msg, __FUNCTION__);
        $array = [];
        self::parseRawHttpRequest($array);
        return $array;
    }
}
