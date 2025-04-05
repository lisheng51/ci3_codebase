<?php

class EnvironmentModel
{
    public static $allow_ips_1 = '127.0.0.1,::1';
    public static $allow_ips_2 = '';
    public static $allow_ips_3 = '';
    public static $primaryKey = "environment_id";

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

    protected static function id_1(): array
    {
        $data[self::$primaryKey] = 1;
        $data['name'] = 'Development';
        $data['description'] = null;
        $data['allow_ips'] = self::$allow_ips_1;
        return $data;
    }

    protected static function id_2(): array
    {
        $data[self::$primaryKey] = 2;
        $data['name'] = 'Productie';
        $data['description'] = null;
        $data['allow_ips'] = self::$allow_ips_2;
        return $data;
    }

    protected static function id_3(string $key = ''): array
    {
        $data[self::$primaryKey] = 3;
        $data['name'] = 'Test';
        $data['description'] = null;
        $data['allow_ips'] = self::$allow_ips_3;
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

    public static function fetchAllowIps(int $id = 0): string
    {
        return self::fetchData($id, 'allow_ips') ?? '';
    }
}
