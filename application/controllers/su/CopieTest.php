<?php

class CopieTest extends Su_Controller
{

    private $url = 'https://www.bloemendaalconsultancy.nl/api/Database/copie';

    public function index(string $truncate = 'yes')
    {
        $url = $this->url;
        $tables[] = "bc_api";
        $tables[] = "bc_login";
        $tables[] = "bc_config";
        $tables[] = "bc_module";
        $tables[] = "bc_permission";
        $tables[] = "bc_permission_group";
        $tables[] = "bc_user";
        $tables[] = "bc_bookmark";
        ini_set('max_execution_time', 0); // for infinite time of execution 
        foreach ($tables as $table) {
            $fields_string = http_build_query(['table' => $table]);
            $stream = fopen($url . '?' . $fields_string, 'r');
            $totalcount = (int) stream_get_contents($stream);
            //echo $totalcount;
            //exit;
            fclose($stream);
            $curl_url = $url . '/list?' . $fields_string;
            if ($totalcount > 0) {
                $pagecount = 5000;
                if ($truncate === 'yes') {
                    $this->db->truncate($table);
                }
                for ($i = 1; $i <= intval($totalcount / $pagecount) + 1; $i++) {
                    $fields_string = http_build_query(['limit' => $pagecount, 'page' => $i]);
                    $result = get_curl($curl_url . '&' . $fields_string);
                    $listdb = json_decode($result, true);
                    $this->db->insert_batch($table, $listdb);
                    echo 'Run => ' . $table . ' => ' . $i;
                    echo "<br>";
                    ob_flush();
                    flush();
                    unset($listdb);
                }
            }
        }
    }

    public function all($onlytoday = 'n')
    {
        $tables = $this->db->list_tables();
        $this->benchmark->mark('code_start');
        $ckT[] = 'bc_app_log';
        $ckT[] = 'bc_api_log';
        $ckT[] = 'bc_history_url';
        $ckT[] = 'bc_send_mail';
        $ckT[] = 'bc_geo';
        $ckT[] = 'bc_geo_easy';
        $ckT[] = 'bc_province';
        $ckT[] = 'bc_country';
        $ckT[] = 'bc_language';
        foreach ($tables as $table) {
            if (in_array($table, $ckT) === true) {
                continue;
            }
            $fields_string = http_build_query([
                'onlytoday' => $onlytoday,
                'table' => $table,
            ]);
            $stream = fopen($this->url . '?' . $fields_string, 'r');
            $totalcount = (int) stream_get_contents($stream);
            fclose($stream);
            $curl_url = $this->url . '/list?' . $fields_string;
            $this->insertBatch($totalcount, $curl_url, $table, $onlytoday);
        }
        $this->benchmark->mark('code_end');
        $this->load->dbutil();
        $this->dbutil->optimize_database();
        $message = 'Total Execution Time:' . $this->benchmark->elapsed_time('code_start', 'code_end');
        exit($message);
    }

    private function insertBatch($totalcount, $curl_url, $table, $onlytoday)
    {
        if ($totalcount > 0) {
            $pagecount = 2000;
            set_time_limit(0);
            if ($onlytoday === 'y') {
                $this->db->where('DATE(`created_at`)', 'CURDATE()', false)->delete($table);
            } else {
                $this->db->truncate($table);
            }
            for ($i = 1; $i <= intval($totalcount / $pagecount) + 1; $i++) {
                $fields_string = http_build_query(['limit' => $pagecount, 'page' => $i]);
                $result = get_curl($curl_url . '&' . $fields_string);
                $listdb = json_decode($result, true);
                $this->db->insert_batch($table, $listdb);
                unset($listdb);
            }
        }
    }

    public function table($tablename = "api", string $truncate = 'yes')
    {
        $table = 'bc_' . $tablename;
        $fields_string = http_build_query([
            'onlytoday' => 'n',
            'table' => $table
        ]);
        $stream = fopen($this->url . '?' . $fields_string, 'r');
        $totalcount = (int) stream_get_contents($stream);
        fclose($stream);
        $curl_url = $this->url . '/list?' . $fields_string;

        if ($totalcount > 0) {
            $pagecount = 5000;
            ini_set('max_execution_time', 0); // for infinite time of execution 
            set_time_limit(0);
            if ($truncate === 'yes') {
                $this->db->truncate($table);
            }
            for ($i = 1; $i <= intval($totalcount / $pagecount) + 1; $i++) {
                $fields_string = http_build_query(['limit' => $pagecount, 'page' => $i]);
                $result = get_curl($curl_url . '&' . $fields_string);
                $listdb = json_decode($result, true);
                $this->db->insert_batch($table, $listdb);
                unset($listdb);
            }
        }
    }

    public function module(string $truncate = 'yes')
    {
        $url = CIInput()->get('url') ?? $this->url;
        $tableinput = CIInput()->get('tables');
        $tables = explode(',', $tableinput);
        if (empty(current($tables)) === true) {
            exit('no table');
        }
        ini_set('max_execution_time', 0); // for infinite time of execution 
        foreach ($tables as $table) {
            $fields_string = http_build_query(['table' => $table]);
            $stream = fopen($url . '?' . $fields_string, 'r');
            $totalcount = (int) stream_get_contents($stream);
            //echo $totalcount;
            //exit;
            fclose($stream);
            $curl_url = $url . '/list?' . $fields_string;
            if ($totalcount > 0) {
                $pagecount = 5000;
                if ($truncate === 'yes') {
                    $this->db->truncate($table);
                }
                for ($i = 1; $i <= intval($totalcount / $pagecount) + 1; $i++) {
                    $fields_string = http_build_query(['limit' => $pagecount, 'page' => $i]);
                    $result = get_curl($curl_url . '&' . $fields_string);
                    if ($result === null) {
                        continue;
                    }
                    $listdb = json_decode($result, true);
                    $this->db->insert_batch($table, $listdb);
                    echo 'Run => ' . $table . ' => ' . $i;
                    echo "<br>";
                    ob_flush();
                    flush();
                    unset($listdb);
                }
            }
        }
    }

    /**
     * 
     * @param http://localhost/intranet/su/CopieTest/database/yes.html?url=http://192.168.16.181/newbc/api/Database/copie
     */
    public function database(string $truncate = 'yes')
    {
        $url = CIInput()->get('url') ?? "";
        $databasefrom = CIInput()->get('from') ?? 'newbc_rdw';
        $databaseto = CIInput()->get('to') ?? 'intranet_testtest';
        $this->db->db_select($databaseto);
        $tables = $this->db->list_tables();
        if (empty(current($tables)) === true || empty($url) === true) {
            exit('parameter error');
        }
        ini_set('max_execution_time', 0); // for infinite time of execution 
        foreach ($tables as $table) {
            $fields_string = http_build_query(['table' => $table, 'database2_name' => $databasefrom]);
            $stream = fopen($url . '?' . $fields_string, 'r');
            $totalcount = (int) stream_get_contents($stream);
            fclose($stream);
            $curl_url = $this->url . '/list?' . $fields_string;
            if ($totalcount > 0) {
                $pagecount = 5000;
                if ($truncate === 'yes') {
                    $this->db->truncate($table);
                }
                for ($i = 1; $i <= intval($totalcount / $pagecount) + 1; $i++) {
                    $fields_string = http_build_query(['limit' => $pagecount, 'page' => $i]);
                    $result = get_curl($curl_url . '&' . $fields_string);
                    $listdb = json_decode($result, true);
                    $this->db->insert_batch($table, $listdb);
                    echo 'Run => ' . $table . ' => ' . $i;
                    echo "<br>";
                    ob_flush();
                    flush();
                    unset($listdb);
                }
            }
        }
    }
}
