<?php

class Home extends Back_Controller
{
    public function index()
    {
        if (CIInput()->post()) {
            $this->engineAction();
        }

        $data["title"] = 'Tabellen naar InnoDB';
        $data['breadcrumb'] = "";
        $data["listdb"] = $this->getNonInnodbTables();
        $data["event_result_box"] = "";
        $this->view_layout("index", $data);
    }

    private function getNonInnodbTables(): array
    {
        $sql = "SELECT TABLE_NAME AS name, ENGINE AS engine, TABLE_ROWS AS row_count
                FROM information_schema.TABLES
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_TYPE = 'BASE TABLE'
                  AND (ENGINE IS NULL OR ENGINE <> 'InnoDB')
                ORDER BY TABLE_NAME";
        return CIDb()->query($sql)->result_array();
    }

    private function engineAction()
    {
        $action = CIInput()->post("action");

        if ($action === "list") {
            $json["tables"] = array_column($this->getNonInnodbTables(), "name");
            $json["status"] = "good";
            exit(json_encode($json));
        }

        if ($action === "convert") {
            $table = (string)CIInput()->post("table");
            if (in_array($table, array_column($this->getNonInnodbTables(), "name"), true) === false) {
                $json["msg"] = "Tabel $table is al InnoDB of bestaat niet";
                $json["status"] = "warning";
                exit(json_encode($json));
            }

            CIDb()->db_debug = false;
            $quoted = '`' . str_replace('`', '``', $table) . '`';
            if (CIDb()->query("ALTER TABLE $quoted ENGINE=InnoDB") === false) {
                $error = CIDb()->error();
                $json["msg"] = "Tabel $table: " . $error["message"];
                $json["status"] = "error";
                exit(json_encode($json));
            }

            $json["msg"] = "Tabel $table is omgezet naar InnoDB";
            $json["status"] = "good";
            exit(json_encode($json));
        }

        $json["msg"] = "Onbekende actie";
        $json["status"] = "error";
        exit(json_encode($json));
    }
}
