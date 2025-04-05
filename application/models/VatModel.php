<?php

class VatModel
{
    public static $primaryKey = "vat_id";
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
        $data['name'] = '0%';
        $data['description'] = null;
        $data['number'] = 0.00;
        return $data;
    }

    protected static function id_2(): array
    {
        $data[self::$primaryKey] = 2;
        $data['name'] = '21%';
        $data['description'] = null;
        $data['number'] = 1.21;
        return $data;
    }

    protected static function id_3(): array
    {
        $data[self::$primaryKey] = 3;
        $data['name'] = '9%';
        $data['description'] = null;
        $data['number'] = 1.09;
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

    public static function fetchNumber(int $id = 0)
    {
        return self::fetchData($id, 'number') ?? 0;
    }

    public static function calAmount(int $amount = 0, int $id = 0, bool $with_vat = true)
    {
        if ($amount < 0.01) {
            return 0;
        }

        $cal_number = self::fetchNumber($id);
        if ($cal_number < 0.01) {
            return 0;
        }
        if ($with_vat === false) {
            $cal_number = $cal_number - 1;
            return round($amount - $amount * $cal_number, 2);
        }
        return round($amount - $amount / $cal_number, 2);
    }
}
