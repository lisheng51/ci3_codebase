<?php

class UploadModel
{

    use BasicModel;
    public static $rootFolder = ENVIRONMENT_UPLOAD_PATH;
    public static $folder = "files";
    private static $fileName = "";
    private static $base64code = "";
    public static $usingResize = true;

    public static function __constructStatic()
    {
        self::$primaryKey = "upload_id";
        self::$table = 'upload';
        self::$selectOrderBy = [
            'upload_id#desc' => 'ID (aflopend)',
            'title#desc' => 'Title (aflopend)',
            'title#asc' => 'Title (oplopend)',
            'file_name#desc' => 'Bestand (aflopend)',
            'file_name#asc' => 'Bestand (oplopend)',
        ];
    }

    public static function makeDir(string $dir = "", bool $withIndexFile = true, bool $withLockFile = false)
    {
        $path = FCPATH . self::$rootFolder;
        if (empty($dir) === false) {
            $path .= DIRECTORY_SEPARATOR . $dir;
        }
        if (is_dir($path) === false) {
            mkdir($path, 0755, true);
        }

        if ($withIndexFile) {
            file_put_contents($path . DIRECTORY_SEPARATOR . 'index.html', '<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body>Directory access is forbidden</body></html>');
        }

        if ($withLockFile) {
            file_put_contents($path . DIRECTORY_SEPARATOR . '.htaccess', 'deny from all');
        }
    }

    public static function addBatch(array $data = [])
    {
        CIDb()->insert_batch(self::$table, $data);
    }

    public static function getPostdata(): array
    {
        $data[UploadTypeModel::$primaryKey] = CIInput()->post(UploadTypeModel::$primaryKey);
        $data["title"] = CIInput()->post("title");
        return $data;
    }

    public static function fromString(string $base64code = "", string $file_name_format = 'uploadfile'): string
    {
        if (empty($base64code)) {
            return '';
        }

        self::$fileName = $file_name_format;
        self::$base64code = $base64code;
        if (preg_match("/^data:image\/png;base64/i", self::$base64code)) {
            return self::makeFile('png');
        } elseif (preg_match("/^data:application\/pdf;base64/i", self::$base64code)) {
            return self::makeFile('pdf');
        } elseif (preg_match("/^data:image\/jpeg;base64/i", self::$base64code)) {
            return self::makeFile('jpeg');
        } elseif (preg_match("/^data:image\/jpg;base64/i", self::$base64code)) {
            return self::makeFile('jpg');
        } elseif (preg_match("/^data:text\/xml;base64/i", self::$base64code)) {
            return self::makeFile('xml');
        } elseif (preg_match("/^data:image\/gif;base64/i", self::$base64code)) {
            return self::makeFile('gif');
        } elseif (preg_match("/^data:application\/vnd.openxmlformats-officedocument.wordprocessingml.document;base64/i", self::$base64code)) {
            return self::makeFile('docx');
        } elseif (preg_match("/^data:application\/msword;base64/i", self::$base64code)) {
            return self::makeFile('doc');
        } else {
            return self::$base64code;
        }
    }

    public static function showFile(string $file = '', bool $onlyPath = false)
    {
        if (empty($file)) {
            return $onlyPath ? "" : sys_asset_url('img/0.png');
        }
        return self::showDir($file, $onlyPath);
    }

    public static function showDir(string $file = '', bool $onlyPath = true)
    {
        $filePath = self::$rootFolder . DIRECTORY_SEPARATOR . self::$folder . DIRECTORY_SEPARATOR . $file;
        if ($onlyPath) {
            return FCPATH . $filePath;
        }
        $filenameUrl = str_replace('\\', '/', $filePath);
        return base_url($filenameUrl);
    }

    public static function action(string $inputname = '', string $defaultval = ''): string
    {
        if (isset($_FILES[$inputname]) && !empty($_FILES[$inputname]['tmp_name'])) {
            $file = $_FILES[$inputname]['name'];
            $file_ext = self::showDir($file);
            if (move_uploaded_file($_FILES[$inputname]['tmp_name'], $file_ext)) {
                return $file;
            }
        }
        return $defaultval;
    }

    private static function makeFile(string $ext = 'png')
    {
        switch ($ext) {
            case 'png':
            case 'jpeg':
            case 'jpg':
            case 'gif':
                $imagestrng = str_replace('data:image/' . $ext . ';base64,', "", self::$base64code);
                break;
            case 'pdf':
                $imagestrng = str_replace('data:application/' . $ext . ';base64,', "", self::$base64code);
                break;
            case 'xml':
                $imagestrng = str_replace('data:text/' . $ext . ';base64,', "", self::$base64code);
                break;
            case 'docx':
                $imagestrng = str_replace('data:application/vnd.openxmlformats-officedocument.wordprocessingml.document;base64,', "", self::$base64code);
                break;
            case 'doc':
                $imagestrng = str_replace('data:application/msword;base64,', "", self::$base64code);
                break;
        }

        $decode_string = str_replace(' ', '+', $imagestrng);
        $folder = self::$rootFolder . DIRECTORY_SEPARATOR;
        file_put_contents($folder . DIRECTORY_SEPARATOR . self::$folder . DIRECTORY_SEPARATOR . self::$fileName . '.' . $ext, base64_decode($decode_string));
        if ($ext === 'png' || $ext === 'jpeg' || $ext === 'jpg' || $ext === 'gif') {
            self::resize($folder . DIRECTORY_SEPARATOR . self::$folder . DIRECTORY_SEPARATOR . self::$fileName . '.' . $ext);
        }
        return self::$fileName . '.' . $ext;
    }

    public static function resize(string $source_image = '')
    {
        if (self::$usingResize) {
            $config['image_library'] = 'gd2';
            $config['source_image'] = $source_image;
            $config['create_thumb'] = false;
            $config['maintain_ratio'] = true;
            $config['width'] = 450;
            $config['height'] = 450;
            CIImageLib()->initialize($config);
            if (!CIImageLib()->resize()) {
                echo CIImageLib()->display_errors();
            }
            CIImageLib()->clear();
        }
    }

    public static function convertBase64(string $file = '')
    {
        if (empty($file)) {
            return "";
        }
        $type = pathinfo($file, PATHINFO_EXTENSION);
        $data = @file_get_contents($file);
        if ($data === false) {
            return "";
        }
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    public static function replaceSiteUrl(string $content = '', string $search = '', string $replace = '')
    {
        if (empty($replace)) {
            $replace = base_url();
        }
        //replace_site_url($content, http://localhost');
        return preg_replace('/' . preg_quote($search, '/') . '/', $replace, $content);
    }
}
