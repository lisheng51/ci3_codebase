<?php

class Upload extends Ajax_Controller
{

    public function tinymce()
    {
        $user_id = CIInput()->post("user_id") ?? 0;
        $base64 = CIInput()->post("base64") ?? "";
        if (empty($base64)) {
            $json["result"] =  "";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $fileData = base64_decode($base64);
        if (strlen($fileData) > maxUploadByte()) {
            $json["result"] =  "";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $origin_filename = CIInput()->post("name") ?? time() . '.png';
        $ext = pathinfo($origin_filename, PATHINFO_EXTENSION);
        $name = time() . '.' . $ext;

        $filename = UploadTypeModel::showDir("", $user_id) . $name;
        file_put_contents($filename, $fileData);
        $filenameUrl = str_replace('\\', '/', $filename);
        $json["result"] =  base_url($filenameUrl);
        $json["status"] = "good";
        exit(json_encode($json));
    }

    public function delete()
    {
        $json["result"] =  "";
        $json["status"] = "good";
        exit(json_encode($json));
    }

    public function action()
    {
        $inputname = CIInput()->post('field') ?? '';
        if (empty($inputname)) {
            $json["result"] =  "";
            $json["status"] = "error";
            exit(json_encode($json));
        }

        $user_id = CIInput()->post("user_id") ?? 0;
        $dir = UploadTypeModel::showDir("", $user_id);

        $folder =  CIInput()->post('folder') ?? '';
        if (!empty($folder)) {
            $folder = str_replace('/', DIRECTORY_SEPARATOR, $folder);
            $dir = UploadModel::$rootFolder . DIRECTORY_SEPARATOR .  $folder . DIRECTORY_SEPARATOR;
        }

        $defaultValue = '';
        if (isset($_FILES[$inputname]) && !empty($_FILES[$inputname]['tmp_name'])) {
            $file = $_FILES[$inputname]['name'];
            $file_ext = $dir . $file;
            if (move_uploaded_file($_FILES[$inputname]['tmp_name'], $file_ext)) {
                $defaultValue = $file;
            }
        }

        $json["result"] =  $defaultValue;
        $json["status"] = "good";
        exit(json_encode($json));
    }
}
