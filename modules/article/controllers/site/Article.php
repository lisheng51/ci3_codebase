<?php

class Article extends Site_Controller
{
    protected $title_module = 'Artikelen';

    public function index()
    {
        $cateId = (int)$this->input->get(ArticleCateModel::$primaryKey);
        $keyword = trim((string)$this->input->get('q'));

        ArticleModel::sqlReset();
        ArticleModel::joinArticleCate();
        $data_where[ArticleModel::$table . '.is_del'] = 0;
        if ($cateId > 0) {
            $data_where[ArticleModel::$table . '.' . ArticleCateModel::$primaryKey] = $cateId;
        }
        ArticleModel::$sqlWhere = $data_where;
        if ($keyword !== '') {
            ArticleModel::$sqlLike = [ArticleModel::$table . '.title' => $keyword];
        }
        ArticleModel::$sqlOrderBy = [ArticleModel::$table . '.created_at' => 'desc'];

        $total = ArticleModel::getTotal();
        list($limit, $page) = GlobalModel::limitAndPage(9);
        $listdb = [];
        foreach (ArticleModel::getList($limit, $page) as $rs) {
            $listdb[] = $this->formatItem($rs);
        }

        $data["listdb"] = $listdb;
        $data["total"] = $total;
        $data["pagination"] = GlobalModel::showPage($total, 9);
        $data["catedb"] = $this->getCateList();
        $data["cate_id"] = $cateId;
        $data["keyword"] = $keyword;
        $data["title"] = $this->title_module;
        $data["breadcrumbDataReset"] = [
            ["name" => "Home", "url" => "/"],
            ["name" => $this->title_module, "url" => null],
        ];
        $this->view_layout("index", $data);
    }

    public function view($id = 0)
    {
        if (is_numeric($id) === false) {
            redirect($this->controller_url);
        }
        ArticleModel::sqlReset();
        ArticleModel::joinArticleCate();
        $rsdb = ArticleModel::getOneByFields([
            ArticleModel::$table . '.' . ArticleModel::$primaryKey => (int)$id,
            ArticleModel::$table . '.is_del' => 0,
        ]);
        if (empty($rsdb)) {
            redirect($this->controller_url);
        }
        ArticleModel::updateHits($rsdb);

        $data["rsdb"] = $this->formatItem($rsdb);
        $data["recentdb"] = $this->getRecentList((int)$id);
        $data["catedb"] = $this->getCateList();
        $data["title"] = $rsdb["title"];
        $data["description"] = $rsdb["description"];
        $data["breadcrumbDataReset"] = [
            ["name" => "Home", "url" => "/"],
            ["name" => $this->title_module, "url" => $this->controller_url],
            ["name" => html_escape($rsdb["cate_name"]), "url" => $this->controller_url . '?' . ArticleCateModel::$primaryKey . '=' . $rsdb[ArticleCateModel::$primaryKey]],
            ["name" => html_escape($rsdb["title"]), "url" => null],
        ];
        $this->view_layout("view", $data);
    }

    private function formatItem(array $rs = []): array
    {
        $text = trim(html_entity_decode(strip_tags($rs["content"]), ENT_QUOTES, 'UTF-8'));
        $rs["url"] = site_url($this->controller_url . "/view/" . $rs[ArticleModel::$primaryKey]);
        $rs["cate_url"] = site_url($this->controller_url . '?' . ArticleCateModel::$primaryKey . '=' . $rs[ArticleCateModel::$primaryKey]);
        $rs["date"] = F_datetime::convert_date($rs["created_at"]);
        $rs["summary"] = empty($rs["description"]) ? character_limiter($text, 160) : $rs["description"];
        $rs["read_minutes"] = max(1, (int)ceil(str_word_count($text) / 200));
        return $rs;
    }

    private function getCateList(): array
    {
        ArticleCateModel::sqlReset();
        ArticleCateModel::$sqlWhere = [ArticleCateModel::$fieldIsDel => 0];
        ArticleCateModel::$sqlOrderBy = ['sort_list' => 'asc', 'name' => 'asc'];
        $listdb = [];
        foreach (ArticleCateModel::getAll() as $rs) {
            $rs["url"] = site_url($this->controller_url . '?' . ArticleCateModel::$primaryKey . '=' . $rs[ArticleCateModel::$primaryKey]);
            $listdb[] = $rs;
        }
        return $listdb;
    }

    private function getRecentList(int $excludeId = 0): array
    {
        ArticleModel::sqlReset();
        ArticleModel::$sqlWhere = [
            'is_del' => 0,
            ArticleModel::$primaryKey . ' !=' => $excludeId,
        ];
        ArticleModel::$sqlOrderBy = ['created_at' => 'desc'];
        $listdb = [];
        foreach (ArticleModel::getList(5) as $rs) {
            $rs["url"] = site_url($this->controller_url . "/view/" . $rs[ArticleModel::$primaryKey]);
            $rs["date"] = F_datetime::convert_date($rs["created_at"]);
            $listdb[] = $rs;
        }
        return $listdb;
    }
}
