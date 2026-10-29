<?php

class ArticleCateModel
{

    use BasicModel;
    public static function __constructStatic()
    {

        self::$table = "article_cate";
        self::$primaryKey = "cate_id";
        self::$selectOrderBy = [
            'cate_id#desc' => 'ID (aflopend)',
            'name#desc' => 'Naam (aflopend)',
            'name#asc' => 'Naam (oplopend)'
        ];
    }

    public static function getExist(array $input = [], int $id = 0): array
    {
        $data['name'] = $input['name'];
        $data[self::$primaryKey . '!='] = $id;
        self::$sqlWhere = $data;
        return self::getOne();
    }

    public static function select(int $id = 0, bool $with_empty = false, string $name = ''): string
    {
        $select_name = empty($name) === false ? $name : self::$primaryKey;
        if ($id === 0) {
            $id = CIInput()->post_get($select_name) ?? 0;
        }
        self::sqlReset();
        $data[self::$fieldIsDel] = 0;
        self::$sqlWhere = $data;
        //$this->sql_order_by = ['sort_list' => 'asc'];
        $select = '<select name=' . $select_name . ' class="form-control selectpicker" data-live-search="true">';
        $select .= $with_empty === true ? "<option value='' >------</option>" : "";
        $listdb = self::getAll();
        foreach ($listdb as $rs) {
            $ckk = $id == $rs[self::$primaryKey] ? "selected" : '';
            $select .= '<option value=' . $rs[self::$primaryKey] . ' ' . $ckk . '>' . $rs["name"] . '</option>';
        }
        $select .= '</select>';
        return $select;
    }

    public static function selectMultiple(array $haystack = [], string $name = ''): string
    {
        $select_name = empty($name) === false ? $name : self::$primaryKey;
        if (empty($haystack)) {
            $haystack = CIInput()->post_get($select_name) ?? [];
            if (is_array($haystack) === false) {
                $haystack = [$haystack];
            }
        }
        self::sqlReset();
        $data[self::$fieldIsDel] = 0;
        self::$sqlWhere = $data;
        //$this->sql_order_by = ['sort_list' => 'asc'];
        $select = '<select name="' . $select_name . '[]" class="form-control selectpicker" data-selected-text-format="count > 3" data-live-search="true" multiple title="------">';
        $listdb = self::getAll();
        foreach ($listdb as $rs) {
            $ckk = (empty($haystack) === false && in_array($rs[self::$primaryKey], $haystack) === true) ? 'selected' : '';
            $select .= '<option value=' . $rs[self::$primaryKey] . ' ' . $ckk . '>' . $rs["name"] . '</option>';
        }
        $select .= '</select>';
        return $select;
    }

    public static function getPostdata(): array
    {
        $data["description"] = CIInput()->post("description");
        $data["name"] = CIInput()->post("name");
        AjaxckModel::value('name', $data["name"]);
        return $data;
    }
}
