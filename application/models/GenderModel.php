<?php

class GenderModel
{
    public static $primaryKey = "gender_id";
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
        if ($id === 0) {
            $id = CIInput()->get(self::$primaryKey) ?? 0;
        }

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
        $data['name'] = '------';
        $data['href'] = "";
        return $data;
    }

    protected static function id_2(): array
    {
        $data[self::$primaryKey] = 2;
        $data['name'] = 'Vrouw';
        $data['href'] = 'Mevrouw';
        return $data;
    }

    protected static function id_3(): array
    {
        $data[self::$primaryKey] = 3;
        $data['name'] = 'Man';
        $data['href'] = 'Meneer';
        return $data;
    }

    protected static function id_4(): array
    {
        $data[self::$primaryKey] = 4;
        $data['name'] = 'Neutraal';
        $data['href'] = '';
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

    public static function fetchName(int $id = 0): string
    {
        return self::fetchData($id, 'name') ?? '';
    }


    public static function fetchGenderId(string $value = ""): int
    {
        switch ($value) {
            case 'male':
                return 3;
            case 'female':
                return 2;
            default:
                return 1;
        }
    }
}
