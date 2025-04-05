<?php

class AddressTypeModel
{
    public static $primaryKey = "address_type_id";
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


    public static function selectPath(string $path = "back", string $name = 'link_dir'): string
    {
        $listdb = self::listDB();
        $select = '<select name=' . $name . ' class="form-control">';
        foreach ($listdb as $rs) {
            $ckk = $path == $rs['path'] ? "selected" : '';
            $select .= '<option value=' . $rs['path'] . ' ' . $ckk . '>' . $rs["path"] . '</option>';
        }
        $select .= '</select>';
        return $select;
    }

    protected static function id_1(): array
    {
        $data[self::$primaryKey] = 1;
        $data['name'] = 'Bezoek';
        $data['description'] = null;
        return $data;
    }

    protected static function id_2(): array
    {
        $data[self::$primaryKey] = 2;
        $data['name'] = 'Post';
        $data['description'] = null;
        return $data;
    }

    protected static function id_3(): array
    {
        $data[self::$primaryKey] = 3;
        $data['name'] = 'Vestiging';
        $data['description'] = null;
        return $data;
    }

    protected static function id_4(): array
    {
        $data[self::$primaryKey] = 4;
        $data['name'] = 'GBA';
        $data['description'] = null;
        return $data;
    }

    protected static function id_5(): array
    {
        $data[self::$primaryKey] = 5;
        $data['name'] = 'Factuur';
        $data['description'] = null;
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
}
