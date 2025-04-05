<?php

class Database extends MX_Controller
{

    public function processlist()
    {
        $this->load->database();
        $query = $this->db->query("Show PROCESSLIST");
        $listdb = $query->result_array();
        $total = count($listdb);
        $output["total"] = $total;
        exit(json_encode($output));
    }

    public function error()
    {
        $listdb = directory_map(FCPATH . 'logs');
        $output["listdb"] = $listdb;
        exit(json_encode($output));
    }

    public function max_user_connections()
    {
        $this->load->database();
        $query = $this->db->query("Show variables like 'max_user_connections'");
        $rsdb = $query->row_array();
        $output["total"] = (int) $rsdb['Value'];
        exit(json_encode($output));
    }

    public function copie($par = "")
    {
        $this->load->database();
        $table = CIInput()->get("table");
        $onlytoday = CIInput()->get("onlytoday");
        $database2_name = CIInput()->get("database2_name");
        if ($database2_name !== null) {
            $this->db->db_select($database2_name);
        }
        $tables = $this->db->list_tables();
        if (empty($table) || !in_array($table, $tables)) {
            exit("no table");
        }

        if ($par == 'list') {
            $this->fetchList($table, $onlytoday);
        }
        $this->fetchTotal($table, $onlytoday);
    }

    private function fetchList($table, $onlytoday = 'n')
    {
        $page_limit = CIInput()->get("limit");
        $limit = empty($page_limit) ? c_key('webapp_default_show_per_page') : $page_limit;
        $page_number = CIInput()->get("page");
        $page = empty($page_number) ? 0 : ($page_number * $limit) - $limit;
        $query = $this->db->get($table, $limit, $page);
        if ($onlytoday === 'y') {
            $query = $this->db->where('DATE(`created_at`)', 'CURDATE()', false)->get($table, $limit, $page);
        }
        $listdb = $query->result_array();
        exit(json_encode($listdb));
    }

    private function fetchTotal($table, $onlytoday = 'n')
    {
        $total = $this->db->count_all_results($table);
        if ($onlytoday === 'y') {
            $total = $this->db->where('DATE(`created_at`)', 'CURDATE()', false)->count_all_results($table);
        }
        exit("$total");
    }
}
