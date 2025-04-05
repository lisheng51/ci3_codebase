<?php
class Post_controller
{

    public function db_query_log()
    {
        $CI = &get_instance();
        $CI->load->database();
        $log_file_extension = config_item('log_file_extension');
        $log_path = config_item('log_path');
        $filepath = $log_path . 'sql-' . date('Y-m-d') . '.' . $log_file_extension;
        $times = $CI->db->query_times;
        $listdb = $CI->db->queries;
        if (!empty($listdb)) {
            $handle = fopen($filepath, "a+");
            foreach ($listdb as $key => $query) {
                $data["time"] = date('H:i:s');
                $data["query"] = $query;
                $data["duration"] = $times[$key];
                $data["uri"] = uri_string();
                $string = json_encode($data);
                fwrite($handle, $string . PHP_EOL);
            }
            fclose($handle);
        }
    }
}
