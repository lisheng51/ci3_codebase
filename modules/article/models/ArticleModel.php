<?php

class ArticleModel
{

    use BasicModel;
    public static function __constructStatic()
    {

        self::$table = "article";
        self::$primaryKey = "article_id";
        self::$selectOrderBy = [
            'article_id#desc' => 'ID (aflopend)',
            'title#desc' => 'Titel (aflopend)',
            'title#asc' => 'Titel (oplopend)'
        ];
    }

    public static function getExist(array $input = [], int $id = 0): array
    {
        $data['title'] = $input['title'];
        $data[ArticleCateModel::$primaryKey] = $input[ArticleCateModel::$primaryKey];
        $data[self::$primaryKey . '!='] = $id;
        self::$sqlWhere = $data;
        return self::getOne();
    }

    public static function joinArticleCate()
    {
        self::$sqlSelect =
            [
                [ArticleCateModel::$table => 'name as cate_name'],
                [ArticleModel::$table => '*']
            ];

        self::$sqlJoin =
            [
                [ArticleCateModel::$table => ArticleCateModel::$table . '.' . ArticleCateModel::$primaryKey . ' = ' . self::$table . '.' . ArticleCateModel::$primaryKey]
            ];
    }

    public static function updateHits(array $rsdb = [])
    {
        $tem_key = "is_view_article_" . $rsdb[self::$primaryKey];
        $session = CISession()->tempdata($tem_key);
        if (empty($session) === TRUE) {
            $data["hits"] = $rsdb["hits"] + 1;
            self::edit($rsdb[self::$primaryKey], $data);
            CISession()->set_tempdata($tem_key, CIInput()->ip_address(), 60);
        }

        return;
    }

    public static function getPostdata(): array
    {
        $data["description"] = CIInput()->post("description");
        $data["title"] = CIInput()->post("title");
        $data["cate_id"] = CIInput()->post("cate_id");
        $data["content"] = CIInput()->post("content");
        $data["status_id"] = 1;
        $data["url_title"] = url_title($data["title"]);
        $data["created_at"] = oneStepCkDatetime('created_at');
        AjaxckModel::value('datetime', $data["created_at"], "Datum is niet juist");
        AjaxckModel::value('title', $data["title"]);
        AjaxckModel::value('cate_id', $data["cate_id"]);
        AjaxckModel::value('status_id', $data["status_id"]);
        AjaxckModel::value('content', $data["content"]);
        return $data;
    }
}
