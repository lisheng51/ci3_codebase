<?php

class Error_log extends Back_Controller
{

    protected $title_module = 'Foutmelding';

    #[DisplayAttribute('Foutmelding overzicht', 'Foutmelding overzicht')]
    public function index()
    {
        // log-*: CI 3.2 format, error-*: old JSON format
        $result = $this->getData(['log', 'error'], [$this, 'parseErrorLine']);
        $data["total"] = $result["total"];
        $data["pagination"] = GlobalModel::showPage($data["total"]);
        $data["listdb"] = $result["listdb"];
        $data["result"] = $this->view_layout_return("ajax_list", $data);
        if (CIInput()->post()) {
            $json["result"] = $data["result"];
            exit(json_encode($json));
        }

        $data["title"] = $this->title_module . ' overzicht';
        $data["event_result_box"] = '';
        $this->view_layout("index", $data);
    }

    #[DisplayAttribute('SQL overzicht', 'SQL overzicht')]
    public function sql()
    {
        $result = $this->getData(['sql'], [$this, 'parseSqlLine']);
        $data["total"] = $result["total"];
        $data["pagination"] = GlobalModel::showPage($data["total"]);
        $data["listdb"] = $result["listdb"];
        $data["result"] = $this->view_layout_return("ajax_list_sql", $data);
        if (CIInput()->post()) {
            $json["result"] = $data["result"];
            exit(json_encode($json));
        }

        $data["title"] = 'SQL overzicht';
        $data["event_result_box"] = '';
        $this->view_layout("index", $data);
    }

    #[DisplayAttribute('Foutmelding downloaden', '')]
    public function download()
    {
        $fileName = basename(CIInput()->get('fileName') ?? '');
        $file = config_item('log_path') . $fileName;
        if (!preg_match($this->filePattern(['log', 'error', 'sql']), $fileName) || !is_file($file)) {
            redirect($this->controller_url);
        }

        force_download($fileName, file_get_contents($file));
    }

    private function getData(array $prefixes, callable $parser): array
    {
        $date_start = "";
        $date_end = "";
        $reportrange = CIInput()->post("reportrange") ?? "";
        if (!empty($reportrange)) {
            $arr_range = explode("t/m", $reportrange);
            $date_start = strtotime(trim($arr_range[0]));
            $date_end = strtotime(trim($arr_range[1] ?? $arr_range[0]));
        }

        list($limit, $page) = GlobalModel::limitAndPage();
        $log_path = config_item('log_path');

        $arrResult = [];
        $files = directory_map($log_path, 1);
        if (empty($files)) {
            return ["total" => 0, "listdb" => $arrResult];
        }

        $pattern = $this->filePattern($prefixes);
        foreach ($files as $fileName) {
            if (!preg_match($pattern, $fileName, $match)) {
                continue;
            }

            $date = $match[1];
            $timestamp = strtotime($date);
            if (!empty($date_start) && $timestamp < $date_start || !empty($date_end) && $timestamp > $date_end) {
                continue;
            }

            $items = $this->readFile($log_path . $fileName, $parser);
            if (empty($items)) {
                continue;
            }

            $arrResult[] = [
                "date" => $date,
                "fileName" => $fileName,
                "downloadButton" => viewButton($this->controller_name . '.download', $this->controller_url . "/download?fileName=" . rawurlencode($fileName), 'Download'),
                "listdb" => $items,
                "count" => count($items),
            ];
        }

        usort($arrResult, function ($a, $b) {
            return strcmp($b["date"], $a["date"]) ?: strcmp($a["fileName"], $b["fileName"]);
        });

        return [
            "total" => count($arrResult),
            "listdb" => array_slice($arrResult, $page, $limit),
        ];
    }

    private function filePattern(array $prefixes): string
    {
        $ext = preg_quote(ltrim((string) config_item('log_file_extension'), '.') ?: 'php', '/');
        return '/^(?:' . implode('|', array_map('preg_quote', $prefixes)) . ')-(\d{4}-\d{2}-\d{2})\.' . $ext . '$/i';
    }

    private function readFile(string $file, callable $parser): array
    {
        $items = [];
        $handle = fopen($file, 'r');
        if ($handle === false) {
            return $items;
        }

        while (($line = fgets($handle)) !== false) {
            $line = rtrim($line, "\r\n");
            if ($line === '' || strpos($line, '<?php') === 0) {
                continue;
            }

            $item = $parser($line);
            if ($item !== null) {
                $items[] = $item;
            } elseif (isset($items[count($items) - 1]['message'])) {
                // Continuation of a multi-line message
                $items[count($items) - 1]['message'] .= PHP_EOL . $line;
            }
        }
        fclose($handle);
        return $items;
    }

    private function parseErrorLine(string $line): ?array
    {
        // CI 3.2: "ERROR - 2026-10-11 14:33:07 --> message"
        if (preg_match('/^(ERROR|DEBUG|INFO|ALL) - (.+?) --> (.*)$/s', $line, $m)) {
            return [
                'level' => $m[1],
                'time' => $m[2],
                'message' => $m[3],
            ];
        }

        // Old MY_Log JSON line
        $rs = json_decode($line, true);
        if (is_array($rs) && isset($rs['message'])) {
            $info = array_filter([
                $rs['uri'] ?? '',
                !empty($rs['post']) && $rs['post'] !== '[]' ? 'POST: ' . $rs['post'] : '',
                !empty($rs['get']) && $rs['get'] !== '[]' ? 'GET: ' . $rs['get'] : '',
            ]);
            return [
                'level' => 'ERROR',
                'time' => $rs['time'] ?? '',
                'message' => $rs['message'] . (empty($info) ? '' : PHP_EOL . implode(PHP_EOL, $info)),
            ];
        }

        return null;
    }

    private function parseSqlLine(string $line): ?array
    {
        $rs = json_decode($line, true);
        if (!is_array($rs)) {
            return null;
        }

        return [
            'time' => $rs['time'] ?? '',
            'uri' => $rs['uri'] ?? '',
            'duration' => $rs['duration'] ?? '',
            'query' => $rs['query'] ?? '',
        ];
    }
}
