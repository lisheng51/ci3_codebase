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
}
