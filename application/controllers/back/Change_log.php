<?php

class Change_log extends Back_Controller
{

    protected $title_module = 'Change logging';

    #[DisplayAttribute('Change log bekijken', 'Bekijken')]
    public function index(int $moduleId = 0)
    {
        $listdb = GlobalModel::getInfo();
        $lastArr = end($listdb);
        $data["title"] = $this->title_module . " overzicht - Systeem " . $lastArr["name"];

        $rsdb = ModuleModel::getOneById($moduleId);
        if (!empty($rsdb)) {
            $data["title"] = $this->title_module . " overzicht - " . $rsdb["path_description"];
            $listdb = GlobalModel::getInfo($rsdb["path"]);
        }

        $total = count($listdb);
        $data["listdb"] = $this->sortList($listdb);
        $data["pagination"] = GlobalModel::showPage($total);
        $data["breadcrumb"] = "";
        $data["event_result_box"] = "";
        $this->view_layout("index", $data);
    }

    private function sortList(array $arr_result_data = [], int $page_limit = 0, string $sort_by_as = "created_at_asc")
    {
        if (empty($arr_result_data)) {
            return $arr_result_data;
        }

        if ($sort_by_as == "created_at_asc") {
            usort($arr_result_data, function ($a, $b) {
                return strtotime($b["created_at"]) - strtotime($a["created_at"]);
            });
        }
        if ($sort_by_as == "created_at_desc") {
            usort($arr_result_data, function ($a, $b) {
                return strtotime($a["created_at"]) - strtotime($b["created_at"]);
            });
        }
        list($limit, $page) = GlobalModel::limitAndPage($page_limit);
        return array_slice($arr_result_data, $page, $limit);
    }
}
