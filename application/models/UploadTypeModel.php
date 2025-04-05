<?php

class UploadTypeModel
{
    public static $primaryKey = "type_id";
    public static function listDB(): array
    {
        $reflector = new \ReflectionClass(static::class);
        $methods  = $reflector->getMethods(ReflectionMethod::IS_PROTECTED);
        $data = [];
        foreach ($methods as $method) {
            $methodNameReflecting = $method->getName();
            $data[] = static::$methodNameReflecting();
        }
        return $data;
    }


    public static function select(int $id = 0, bool $with_empty = false, string $name = ''): string
    {
        $listdb = self::listDB();
        $select_name = !empty($name) ? $name : self::$primaryKey;
        $select = '<select name="' . $select_name . '" class="form-control">';
        $select .= $with_empty === true ? "<option value='' >------</option>" : "";
        foreach ($listdb as $rs) {
            $ckk = $id == $rs[self::$primaryKey] ? "selected" : '';
            $select .= '<option value=' . $rs[self::$primaryKey] . ' ' . $ckk . '>' . $rs["name"] . '</option>';
        }
        $select .= '</select>';
        return $select;
    }

    public static function selectMultiple(array $haystack = []): string
    {
        if (empty($haystack)) {
            $haystack = CIInput()->get(self::$primaryKey) ?? [];
            if (!is_array($haystack)) {
                $haystack = [$haystack];
            }
        }
        $listdb = self::listDB();
        $select = '<select name="' . self::$primaryKey . '[]" class="form-control" multiple>';
        foreach ($listdb as $rs) {
            $ckk = (!empty($haystack) && in_array($rs[self::$primaryKey], $haystack)) ? 'selected' : '';
            $select .= '<option value=' . $rs[self::$primaryKey] . ' ' . $ckk . '>' . $rs["name"] . '</option>';
        }
        $select .= '</select>';
        return $select;
    }


    protected static function id_1(): array
    {
        $data[self::$primaryKey] = 1;
        $data['name'] = 'Any';
        $data['path'] = 'any';
        $data['allowed_types'] = 'kml|kmz|mp3|wma|wmv|mp4|doc|docx|pdf|gif|xlsx|csv|jpeg|jpg|png|txt|zip|bmp|css|js|woff2|ttf|woff|svg|otf';
        return $data;
    }

    protected static function id_2(): array
    {
        $data[self::$primaryKey] = 2;
        $data['name'] = 'Media';
        $data['path'] = 'media';
        $data['allowed_types'] = 'wmv|mp4|gif|jpeg|jpg|png|mp3|wma|bmp|ogg|wav';
        return $data;
    }

    protected static function id_3(): array
    {
        $data[self::$primaryKey] = 3;
        $data['name'] = 'Office';
        $data['path'] = 'office';
        $data['allowed_types'] = 'doc|docx|pdf|xlsx|csv|txt|ppt';
        return $data;
    }

    public static function fetchData(int $id = 0, string $field = '')
    {
        $func = "id_$id";
        $reflector = new \ReflectionClass(static::class);
        if ($reflector->hasMethod($func)) {
            $find = static::$func();
            if (empty($field)) {
                return $find;
            }
            return $find[$field] ?? null;
        }
        return null;
    }

    public static function showDir(string $path_name = "", int $user_id = 0): string
    {
        if ($user_id <= 0) {
            $user_id = LoginModel::userId();
        }
        return UploadModel::$rootFolder . DIRECTORY_SEPARATOR . UploadModel::$folder . DIRECTORY_SEPARATOR . $user_id . DIRECTORY_SEPARATOR . $path_name;
    }

    public static function makeDir(int $user_id = 0, string $folder = 'files')
    {
        UploadModel::$folder = $folder;
        $listdb = self::listDB();
        foreach ($listdb as $rs) {
            $path = FCPATH . self::showDir($rs["path"], $user_id);
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }
}
