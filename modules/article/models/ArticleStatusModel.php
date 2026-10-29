<?php

class ArticleStatusModel
{

    use BasicModel;
    public static function __constructStatic()
    {

        self::$table = "article_status";
        self::$primaryKey = "status_id";
    }

    public static function select(int $id = 0, bool $with_empty = false): string
    {
        $select = '<select name=' . self::$primaryKey . ' class="form-control selectpicker">';
        $select .= $with_empty ? "<option value='' >------</option>" : "";
        $listdb = self::getAll();
        foreach ($listdb as $rs) {
            $ckk = $id == $rs[self::$primaryKey] ? "selected" : '';
            $select .= '<option value=' . $rs[self::$primaryKey] . '' . $ckk . '>' . $rs["label"] . '</option>';
        }
        $select .= '</select>';
        return $select;
    }
}
