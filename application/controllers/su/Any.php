<?php

class Any extends Su_Controller
{

    public function index()
    {
        $value = "NL34abna0508285283";
        $ck = F_algorithm::validateIban($value);
        var_dump($ck);
    }

    public function base64()
    {
        $file_ext = sys_asset_url('js/darktheme.js');
        $file_content = file_get_contents($file_ext);
        $base64String = base64_encode($file_content);
        exit($base64String);
    }

    public function datetime()
    {
        $much_strtotime = '+15 minutes';
        $data["expires"] = date('Y-m-d H:i:s', strtotime($much_strtotime));
        $array["token"] = "23232";
        $array["datetime"] = gmdate('Y-m-d\TH:i:s\Z', strtotime($data["expires"]));
        $json["message"] = $array;
        $json["statusCode"] = 100;
        exit(json_encode($json));
    }

    public function aes()
    {
        $valuestring = "606de6b1d4c43e3d89cd89e379b6c8280559ef5393741b0699fb53cb0faa775571f0fad4cf106058e82660facad8278c97f0188bc5c15bcd100466bf20924d47vIG7ciqjGJOXa0n5FFL7BtV3hI7IuvNV0CxSjOeor46mlwyMfRRaTQiQx7FYwvX+smSgpkZLdgMSB7UyMDY47Ji9FRrqISabhmPWB1vNY4vZqYrajebgfrnC62Q4dBzjASZXD2jt6x1PjM03KAILpirgKFI/IR2yD3ekGQ/zEFvs5oONzQISsIraPLTQMZvJwQsVEaBtOTL/PFVe7OBjoVAK6w4e1gnieTBSSAbvSGkK4+LMyJr+V+4MOghMS22b";
        $valuestring = rawurldecode($valuestring);
        $result = AesModel::decrypt($valuestring);
        $arrTokenValue = json_decode($result, true);

        var_dump($arrTokenValue);
    }

    public function makeDir()
    {

        UploadModel::makeDir();
        $path = config_item('cache_path');
        if (is_dir($path) === false) {
            mkdir($path, 0755, true);
            file_put_contents($path . DIRECTORY_SEPARATOR . '.htaccess', 'deny from all');
        }
    }

    public function fibonacci()
    {
        $result = F_algorithm::fibonacci(10);
        var_dump($result);
    }

    public function speedTest(int $limit = 100)
    {
        $array = range(1, 1000000);
        $this->benchmark->mark('code_start');

        for ($x = 1; $x <= 100; $x++) {
            $i = 0;
            $arrayTodo = [];
            foreach ($array as $value) {
                $i++;
                $check = $value % 2;
                if ($check === 0) {
                    if ($i <= $limit) {
                        $arrayTodo[] = $value;
                    }
                }
            }
        }

        //var_dump($arrayTodo);
        $this->benchmark->mark('code_end');
        $message = 'Total Execution Time:' . $this->benchmark->elapsed_time('code_start', 'code_end');
        exit($message);
    }

    public function speedTestF(int $limit = 100)
    {
        $array = range(1, 1000000);
        $this->benchmark->mark('code_start');

        for ($x = 1; $x <= 100; $x++) {
            $arrayTodo = [];
            if (count($arrayTodo) < $limit) {
                foreach ($array as $value) {
                    $check = $value % 2;
                    if ($check === 0) {
                        $arrayTodo[] = $value;
                    }
                }
            }
        }

        //var_dump($arrayTodo);
        $this->benchmark->mark('code_end');
        $message = 'Total Execution Time:' . $this->benchmark->elapsed_time('code_start', 'code_end');
        exit($message);
    }

    public function qrcode()
    {
        $data = 'https://www.youtube.com/watch?v=DLzxrzFCyOs&t=43s';
        echo '<img src="' . (new chillerlan\QRCode\QRCode)->render($data) . '" />';
    }

    public function password()
    {
        $passObj = new Password_master();
        $config = array(
            'low_size' => 5,
            'upper_size' => 10,
            'number_size' => 1,
            'special_size' => 1
        );

        $result = $passObj->generator(true, $config);
        die($result);
    }
}
